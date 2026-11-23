<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Business;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransferService
{
    public function submitForApproval(
        StockTransfer $transfer,
        User $actor
    ): StockTransfer {
        return DB::transaction(function () use ($transfer, $actor) {
            $transfer = StockTransfer::query()
                ->lockForUpdate()
                ->findOrFail($transfer->id);

            $this->assertBusinessAccess($actor, $transfer->business_id);

            if ($transfer->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft transfers can be submitted for approval.',
                ]);
            }

            if ((int) $transfer->from_branch_id === (int) $transfer->to_branch_id) {
                throw ValidationException::withMessages([
                    'to_branch_id' => 'Source and destination branches must be different.',
                ]);
            }

            $branchesCount = Branch::query()
                ->where('business_id', $transfer->business_id)
                ->whereIn('id', [
                    $transfer->from_branch_id,
                    $transfer->to_branch_id,
                ])
                ->count();

            if ($branchesCount !== 2) {
                throw ValidationException::withMessages([
                    'branches' => 'Both branches must belong to the same business.',
                ]);
            }

            $items = $transfer->items()->with('productVariation')->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'A transfer must contain at least one product.',
                ]);
            }

            foreach ($items as $item) {
                if (
                    (int) $item->business_id !== (int) $transfer->business_id ||
                    (int) $item->productVariation?->business_id !== (int) $transfer->business_id
                ) {
                    throw ValidationException::withMessages([
                        'items' => 'Every product variation must belong to this business.',
                    ]);
                }

                $requestedQuantity = trim((string) $item->quantity_requested);

                if (
                    !preg_match('/^\d+(?:\.\d{1,4})?$/', $requestedQuantity) ||
                    (float) $requestedQuantity <= 0
                ) {
                    throw ValidationException::withMessages([
                        'items' => 'Requested quantities must be positive numbers with no more than four decimal places.',
                    ]);
                }
            }

            $transfer->update([
                'status' => 'pending_approval',
            ]);

            return $transfer->fresh(['items']);
        });
    }

    public function approve(
        StockTransfer $transfer,
        User $actor
    ): StockTransfer {
        return DB::transaction(function () use ($transfer, $actor) {
            $transfer = StockTransfer::query()
                ->lockForUpdate()
                ->findOrFail($transfer->id);

            $this->assertBusinessAccess($actor, $transfer->business_id);

            if ($transfer->status !== 'pending_approval') {
                throw ValidationException::withMessages([
                    'status' => 'Only transfers awaiting approval can be approved.',
                ]);
            }

            if ((int) $transfer->created_by === (int) $actor->id) {
                throw ValidationException::withMessages([
                    'approved_by' => 'You cannot approve a transfer you created.',
                ]);
            }

            $transfer->update([
                'status' => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]);

            return $transfer->fresh(['items', 'approver']);
        });
    }

    /**
     * Dispatch an approved transfer and deduct source stock.
     */
    public function dispatch(
        StockTransfer $transfer,
        User $actor
    ): StockTransfer {
        return DB::transaction(function () use ($transfer, $actor) {
            $transfer = StockTransfer::query()
                ->lockForUpdate()
                ->findOrFail($transfer->id);

            $this->assertBusinessAccess($actor, $transfer->business_id);

            if ($transfer->status !== 'approved') {
                throw ValidationException::withMessages([
                    'status' => 'Only approved transfers can be dispatched.',
                ]);
            }

            $branchesCount = Branch::query()
                ->where('business_id', $transfer->business_id)
                ->whereIn('id', [
                    $transfer->from_branch_id,
                    $transfer->to_branch_id,
                ])
                ->count();

            if ($branchesCount !== 2) {
                throw ValidationException::withMessages([
                    'branches' => 'Both transfer branches must belong to this business.',
                ]);
            }

            $items = $transfer->items()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'This transfer has no items to dispatch.',
                ]);
            }

            foreach ($items as $item) {
                $variation = $item->productVariation()->first();

                if (
                    !$variation ||
                    (int) $variation->business_id !== (int) $transfer->business_id ||
                    (int) $item->business_id !== (int) $transfer->business_id
                ) {
                    throw ValidationException::withMessages([
                        'items' => 'A product variation does not belong to this business.',
                    ]);
                }

                $quantity = trim((string) $item->quantity_requested);

                if (
                    !preg_match('/^\d+(?:\.\d{1,4})?$/', $quantity) ||
                    (float) $quantity <= 0
                ) {
                    throw ValidationException::withMessages([
                        'items' => 'Transfer quantities must be positive numbers with no more than four decimal places.',
                    ]);
                }

                $inventory = BranchInventory::query()
                    ->where('business_id', $transfer->business_id)
                    ->where('branch_id', $transfer->from_branch_id)
                    ->where('product_variation_id', $item->product_variation_id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    throw ValidationException::withMessages([
                        'stock' => 'A product has no inventory record in the source branch.',
                    ]);
                }

                // The database performs the stock comparison and deduction.
                // This prevents negative stock even during concurrent operations.
                $deducted = BranchInventory::query()
                    ->whereKey($inventory->id)
                    ->where('quantity', '>=', $quantity)
                    ->decrement('quantity', $quantity);

                if ($deducted !== 1) {
                    throw ValidationException::withMessages([
                        'stock' => 'Insufficient stock in the source branch for one or more products.',
                    ]);
                }

                $item->update([
                    'quantity_dispatched' => $quantity,
                ]);

                StockMovement::create([
                    'business_id' => $transfer->business_id,
                    'branch_id' => $transfer->from_branch_id,
                    'product_variation_id' => $item->product_variation_id,
                    'user_id' => $actor->id,
                    'movement_type' => 'transfer_out',
                    'direction' => 'out',
                    'quantity' => $quantity,
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                    'reference_number' => $transfer->reference_number,
                    'notes' => 'Stock dispatched to another branch.',
                    'moved_at' => now(),
                ]);
            }

            $transfer->update([
                'status' => 'in_transit',
                'dispatched_at' => now(),
            ]);

            return $transfer->fresh(['items', 'sourceBranch', 'destinationBranch']);
        });
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
