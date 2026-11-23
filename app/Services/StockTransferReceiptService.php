<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Business;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StockTransferReceipt;
use App\Models\StockTransferReceiptItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransferReceiptService
{
    /**
     * Record a receipt batch and increase destination stock
     * using good quantities only.
     *
     * Each line must contain:
     * stock_transfer_item_id
     * quantity_received
     * quantity_damaged
     * quantity_missing
     * discrepancy_notes (optional)
     */
    public function receiveBatch(
        StockTransfer $transfer,
        User $actor,
        string $receiptNumber,
        array $lines,
        ?string $notes = null
    ): StockTransferReceipt {
        return DB::transaction(function () use (
            $transfer,
            $actor,
            $receiptNumber,
            $lines,
            $notes
        ) {
            $transfer = StockTransfer::query()
                ->lockForUpdate()
                ->findOrFail($transfer->id);

            $this->assertBusinessAccess($actor, $transfer->business_id);

            if ($transfer->status !== 'in_transit') {
                throw ValidationException::withMessages([
                    'status' => 'Only dispatched transfers can be received.',
                ]);
            }

            if (trim($receiptNumber) === '') {
                throw ValidationException::withMessages([
                    'receipt_number' => 'A receipt number is required.',
                ]);
            }

            if (StockTransferReceipt::query()
                ->where('business_id', $transfer->business_id)
                ->where('receipt_number', $receiptNumber)
                ->exists()) {
                throw ValidationException::withMessages([
                    'receipt_number' => 'This receipt number already exists.',
                ]);
            }

            if (empty($lines)) {
                throw ValidationException::withMessages([
                    'items' => 'A receipt must contain at least one item.',
                ]);
            }

            $destinationExists = Branch::query()
                ->whereKey($transfer->to_branch_id)
                ->where('business_id', $transfer->business_id)
                ->exists();

            if (!$destinationExists) {
                throw ValidationException::withMessages([
                    'to_branch_id' => 'The destination branch does not belong to this business.',
                ]);
            }

            $items = $transfer->items()
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $receipt = StockTransferReceipt::create([
                'business_id' => $transfer->business_id,
                'stock_transfer_id' => $transfer->id,
                'receipt_number' => $receiptNumber,
                'received_by' => $actor->id,
                'received_at' => now(),
                'notes' => $notes,
            ]);

            $processedItems = [];

            foreach ($lines as $line) {
                if (!is_array($line) || !isset($line['stock_transfer_item_id'])) {
                    throw ValidationException::withMessages([
                        'items' => 'Every receipt line must identify its transfer item.',
                    ]);
                }

                $itemId = (int) $line['stock_transfer_item_id'];

                if (isset($processedItems[$itemId])) {
                    throw ValidationException::withMessages([
                        'items' => 'A transfer item cannot appear twice in one receipt batch.',
                    ]);
                }

                $processedItems[$itemId] = true;

                $item = $items->get($itemId);

                if (!$item) {
                    throw ValidationException::withMessages([
                        'items' => 'A receipt item does not belong to this transfer.',
                    ]);
                }

                if ((int) $item->business_id !== (int) $transfer->business_id) {
                    throw ValidationException::withMessages([
                        'items' => 'A receipt item belongs to another business.',
                    ]);
                }

                $variation = $item->productVariation()->first();

                if (
                    !$variation ||
                    (int) $variation->business_id !== (int) $transfer->business_id
                ) {
                    throw ValidationException::withMessages([
                        'items' => 'A product variation does not belong to this business.',
                    ]);
                }

                $received = $this->quantityUnits(
                    $line['quantity_received'] ?? 0,
                    'quantity_received'
                );

                $damaged = $this->quantityUnits(
                    $line['quantity_damaged'] ?? 0,
                    'quantity_damaged'
                );

                $missing = $this->quantityUnits(
                    $line['quantity_missing'] ?? 0,
                    'quantity_missing'
                );

                $dispatched = $this->quantityUnits(
                    $item->quantity_dispatched,
                    'quantity_dispatched'
                );

                $previousReceived = $this->quantityUnits(
                    $item->quantity_received,
                    'quantity_received'
                );

                $previousDamaged = $this->quantityUnits(
                    $item->quantity_damaged,
                    'quantity_damaged'
                );

                $previousMissing = $this->quantityUnits(
                    $item->quantity_missing,
                    'quantity_missing'
                );

                $batchTotal = $received + $damaged + $missing;

                if ($batchTotal <= 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Each receipt line must account for a positive quantity.',
                    ]);
                }

                $cumulativeTotal =
                    $previousReceived +
                    $previousDamaged +
                    $previousMissing +
                    $batchTotal;

                if ($cumulativeTotal > $dispatched) {
                    throw ValidationException::withMessages([
                        'items' => 'Received, damaged and missing quantities cannot exceed the dispatched quantity.',
                    ]);
                }

                $hasDiscrepancy = ($damaged + $missing) > 0;

                $receiptItem = StockTransferReceiptItem::create([
                    'business_id' => $transfer->business_id,
                    'stock_transfer_receipt_id' => $receipt->id,
                    'stock_transfer_item_id' => $item->id,
                    'product_variation_id' => $item->product_variation_id,
                    'quantity_received' => $this->formatUnits($received),
                    'quantity_damaged' => $this->formatUnits($damaged),
                    'quantity_missing' => $this->formatUnits($missing),
                    'discrepancy_notes' => $line['discrepancy_notes'] ?? null,
                    'discrepancy_status' => $hasDiscrepancy
                        ? 'pending'
                        : 'not_applicable',
                ]);

                $item->update([
                    'quantity_received' => $this->formatUnits(
                        $previousReceived + $received
                    ),
                    'quantity_damaged' => $this->formatUnits(
                        $previousDamaged + $damaged
                    ),
                    'quantity_missing' => $this->formatUnits(
                        $previousMissing + $missing
                    ),
                ]);

                // Only good quantities enter destination inventory.
                if ($received > 0) {
                    $now = now();

                    // Insert safely if this branch has no inventory row yet.
                    BranchInventory::query()->insertOrIgnore([
                        'business_id' => $transfer->business_id,
                        'branch_id' => $transfer->to_branch_id,
                        'product_variation_id' => $item->product_variation_id,
                        'quantity' => 0,
                        'minimum_stock' => 0,
                        'reorder_level' => 0,
                        'reorder_quantity' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $inventory = BranchInventory::query()
                        ->where('business_id', $transfer->business_id)
                        ->where('branch_id', $transfer->to_branch_id)
                        ->where('product_variation_id', $item->product_variation_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $inventory->increment(
                        'quantity',
                        $this->formatUnits($received)
                    );

                    $inventory->update([
                        'last_stocked_at' => now(),
                    ]);

                    StockMovement::create([
                        'business_id' => $transfer->business_id,
                        'branch_id' => $transfer->to_branch_id,
                        'product_variation_id' => $item->product_variation_id,
                        'user_id' => $actor->id,
                        'movement_type' => 'transfer_in',
                        'direction' => 'in',
                        'quantity' => $this->formatUnits($received),
                        'reference_type' => StockTransferReceipt::class,
                        'reference_id' => $receipt->id,
                        'reference_number' => $receipt->receipt_number,
                        'notes' => 'Good stock received from another branch.',
                        'moved_at' => now(),
                    ]);
                }
            }

            $this->completeIfFullyAccountedFor($transfer, $actor);

            return $receipt->fresh([
                'items',
                'receiver',
                'stockTransfer',
            ]);
        });
    }

    /**
     * Formally resolve a receipt discrepancy.
     */
    public function resolveDiscrepancy(
        StockTransferReceiptItem $receiptItem,
        User $actor,
        string $resolutionNotes
    ): StockTransfer {
        return DB::transaction(function () use (
            $receiptItem,
            $actor,
            $resolutionNotes
        ) {
            $initialItem = StockTransferReceiptItem::query()
                ->findOrFail($receiptItem->id);

            $initialReceipt = StockTransferReceipt::query()
                ->findOrFail($initialItem->stock_transfer_receipt_id);

            $transfer = StockTransfer::query()
                ->lockForUpdate()
                ->findOrFail($initialReceipt->stock_transfer_id);

            $this->assertBusinessAccess($actor, $transfer->business_id);

            if ($transfer->status !== 'in_transit') {
                throw ValidationException::withMessages([
                    'status' => 'Discrepancies can only be resolved while a transfer is in transit.',
                ]);
            }

            $receipt = StockTransferReceipt::query()
                ->whereKey($initialItem->stock_transfer_receipt_id)
                ->where('business_id', $transfer->business_id)
                ->where('stock_transfer_id', $transfer->id)
                ->lockForUpdate()
                ->firstOrFail();

            $receiptItem = StockTransferReceiptItem::query()
                ->whereKey($receiptItem->id)
                ->where('business_id', $transfer->business_id)
                ->where('stock_transfer_receipt_id', $receipt->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($receiptItem->discrepancy_status !== 'pending') {
                throw ValidationException::withMessages([
                    'discrepancy_status' => 'Only pending discrepancies can be resolved.',
                ]);
            }

            if (trim($resolutionNotes) === '') {
                throw ValidationException::withMessages([
                    'resolution_notes' => 'Resolution notes are required.',
                ]);
            }

            $receiptItem->update([
                'discrepancy_status' => 'resolved',
                'resolved_by' => $actor->id,
                'resolved_at' => now(),
                'resolution_notes' => $resolutionNotes,
            ]);

            $this->completeIfFullyAccountedFor($transfer, $actor);

            return $transfer->fresh([
                'items',
                'receipts.items',
                'sourceBranch',
                'destinationBranch',
            ]);
        });
    }

    /**
     * Complete a transfer only when every dispatched quantity
     * has been accounted for and all discrepancies are resolved.
     */
    private function completeIfFullyAccountedFor(
        StockTransfer $transfer,
        User $actor
    ): void {
        $items = $transfer->items()
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($items->isEmpty()) {
            return;
        }

        foreach ($items as $item) {
            $dispatched = $this->quantityUnits(
                $item->quantity_dispatched,
                'quantity_dispatched'
            );

            $accounted =
                $this->quantityUnits($item->quantity_received, 'quantity_received') +
                $this->quantityUnits($item->quantity_damaged, 'quantity_damaged') +
                $this->quantityUnits($item->quantity_missing, 'quantity_missing');

            if ($accounted !== $dispatched) {
                return;
            }
        }

        $hasUnresolvedDiscrepancies = StockTransferReceiptItem::query()
            ->where('business_id', $transfer->business_id)
            ->where(function ($query) {
                $query->where('quantity_damaged', '>', 0)
                    ->orWhere('quantity_missing', '>', 0);
            })
            ->where('discrepancy_status', '!=', 'resolved')
            ->whereHas('receipt', function ($query) use ($transfer) {
                $query->where('stock_transfer_id', $transfer->id);
            })
            ->exists();

        if ($hasUnresolvedDiscrepancies) {
            return;
        }

        $transfer->update([
            'status' => 'completed',
            'received_by' => $actor->id,
            'received_at' => now(),
        ]);
    }

    /**
     * Convert a decimal quantity into integer units of 0.0001.
     * This avoids floating-point arithmetic in quantity comparisons.
     */
    private function quantityUnits(
        mixed $value,
        string $field
    ): int {
        $value = trim((string) $value);

        if (!preg_match('/^\d+(?:\.\d{1,4})?$/', $value)) {
            throw ValidationException::withMessages([
                $field => 'Quantity must be a non-negative number with no more than four decimal places.',
            ]);
        }

        [$whole, $fraction] = array_pad(
            explode('.', $value, 2),
            2,
            ''
        );

        $fraction = str_pad($fraction, 4, '0');

        return ((int) $whole * 10000) + (int) $fraction;
    }

    private function formatUnits(int $units): string
    {
        return intdiv($units, 10000)
            . '.'
            . str_pad((string) ($units % 10000), 4, '0', STR_PAD_LEFT);
    }

    private function assertBusinessAccess(
        User $actor,
        int $businessId
    ): void {
        $isOwner = Business::query()
            ->whereKey($businessId)
            ->where('owner_id', $actor->id)
            ->exists();

        $isActiveMember = $actor->businesses()
            ->where('businesses.id', $businessId)
            ->wherePivot('status', 'active')
            ->exists();

        if (!$isOwner && !$isActiveMember) {
            abort(403, 'You do not have access to this business.');
        }

        $registrar = app(\Spatie\Permission\PermissionRegistrar::class);
        $previousTeamId = $registrar->getPermissionsTeamId();

        try {
            $registrar->setPermissionsTeamId($businessId);

            $actor->unsetRelation('roles');
            $actor->unsetRelation('permissions');

            if (!$actor->hasPermissionTo('inventory.transfer')) {
                abort(403, 'You do not have permission to manage stock transfers.');
            }
        } finally {
            $registrar->setPermissionsTeamId($previousTeamId);
            $actor->unsetRelation('roles');
            $actor->unsetRelation('permissions');
        }
    }
}
