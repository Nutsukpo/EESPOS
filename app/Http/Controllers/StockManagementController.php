<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Business;
use App\Models\ProductVariation;
use App\Models\StockBatch;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockManagementController extends Controller
{
    private function currentBusiness(Request $request): Business
    {
        $business = $request->attributes->get('current_business');

        abort_unless($business instanceof Business, 403);

        return $business;
    }

    private function accessibleBranches(Request $request, Business $business)
    {
        $user = $request->user();

        $query = Branch::query()
            ->where('business_id', $business->id)
            ->where('is_active', true);

        if ((int) $business->owner_id !== (int) $user->id) {
            $query->whereHas('users', function ($users) use ($user) {
                $users->where('users.id', $user->id);
            });
        }

        return $query->orderByDesc('is_main')->orderBy('name');
    }

    public function index(Request $request, Business $business)
    {
        abort_unless($request->user()->can('inventory.view'), 403);

        $business = $this->currentBusiness($request);
        $branches = $this->accessibleBranches($request, $business)->get();

        $selectedBranch = $branches->firstWhere(
            'id',
            (int) $request->query('branch_id')
        ) ?? $branches->first();

        $inventories = collect();

        if ($selectedBranch) {
            $inventories = BranchInventory::query()
                ->where('business_id', $business->id)
                ->where('branch_id', $selectedBranch->id)
                ->with([
                    'productVariation.product',
                    'batches' => function ($query) use ($business, $selectedBranch) {
                        $query->where('business_id', $business->id)
                            ->where('branch_id', $selectedBranch->id)
                            ->orderByRaw('expiry_date IS NULL')
                            ->orderBy('expiry_date')
                            ->orderBy('id');
                    },
                ])
                ->orderBy('id')
                ->paginate(20)
                ->withQueryString();
        }

        $variations = ProductVariation::query()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->whereHas('product', function ($products) use ($business) {
                $products->where('business_id', $business->id)
                    ->where('is_active', true);
            })
            ->with('product:id,name')
            ->orderBy('product_id')
            ->orderBy('name')
            ->get();

        $expiryDays = (int) $request->query('expiry_days', 30);

        if (!in_array($expiryDays, [7, 30, 60, 90], true)) {
            $expiryDays = 30;
        }

        $expiryBatches = collect();
        $expirySummary = [
            'expired_count' => 0,
            'expired_quantity' => 0,
            'near_count' => 0,
            'near_quantity' => 0,
        ];

        if ($selectedBranch) {
            $expiryBatches = StockBatch::query()
                ->where('business_id', $business->id)
                ->where('branch_id', $selectedBranch->id)
                ->where('quantity', '>', 0)
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', today()->addDays(90)->toDateString())
                ->with('productVariation.product')
                ->orderBy('expiry_date')
                ->orderBy('id')
                ->get();

            $expiredBatches = $expiryBatches->filter(
                fn ($batch) => $batch->expiry_date->lt(today())
            );

            $nearExpiryBatches = $expiryBatches->filter(
                fn ($batch) => $batch->expiry_date->gte(today())
                    && $batch->expiry_date->lte(today()->addDays($expiryDays))
            );

            $expirySummary = [
                'expired_count' => $expiredBatches->count(),
                'expired_quantity' => $expiredBatches->sum(
                    fn ($batch) => (float) $batch->quantity
                ),
                'near_count' => $nearExpiryBatches->count(),
                'near_quantity' => $nearExpiryBatches->sum(
                    fn ($batch) => (float) $batch->quantity
                ),
            ];
        }

        return view('businesses.stock.index', compact(
            'business',
            'branches',
            'selectedBranch',
            'inventories',
            'variations',
            'expiryDays',
            'expiryBatches',
            'expirySummary'
        ));
    }

    public function receive(Request $request, Business $business)
    {
        abort_unless($request->user()->can('inventory.adjust'), 403);

        $business = $this->currentBusiness($request);

        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
            'product_variation_id' => ['required', 'integer'],
            'quantity' => [
                'required',
                'numeric',
                'gt:0',
                'regex:/^\d+(?:\.\d{1,4})?$/',
            ],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $branch = $this->accessibleBranches($request, $business)
            ->whereKey($validated['branch_id'])
            ->firstOrFail();

        $variation = ProductVariation::query()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->whereKey($validated['product_variation_id'])
            ->whereHas('product', function ($products) use ($business) {
                $products->where('business_id', $business->id)
                    ->where('is_active', true);
            })
            ->firstOrFail();

        DB::transaction(function () use (
            $validated,
            $business,
            $branch,
            $variation,
            $request
        ) {
            $inventory = BranchInventory::query()
                ->where('business_id', $business->id)
                ->where('branch_id', $branch->id)
                ->where('product_variation_id', $variation->id)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                $inventory = BranchInventory::create([
                    'business_id' => $business->id,
                    'branch_id' => $branch->id,
                    'product_variation_id' => $variation->id,
                    'quantity' => 0,
                    'minimum_stock' => 0,
                ]);

                $inventory = BranchInventory::query()
                    ->whereKey($inventory->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $quantity = round((float) $validated['quantity'], 4);

            $batch = StockBatch::create([
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'product_variation_id' => $variation->id,
                'batch_number' => $validated['batch_number'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'received_quantity' => $quantity,
                'quantity' => $quantity,
                'received_at' => now(),
                'created_by' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            $inventory->quantity = round(
                (float) $inventory->quantity + $quantity,
                4
            );
            $inventory->last_stocked_at = now();
            $inventory->save();

            StockMovement::create([
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'product_variation_id' => $variation->id,
                'stock_batch_id' => $batch->id,
                'user_id' => $request->user()->id,
                'movement_type' => 'purchase',
                'direction' => 'in',
                'quantity' => $quantity,
                'reference_type' => StockBatch::class,
                'reference_id' => $batch->id,
                'reference_number' => $batch->batch_number,
                'notes' => $validated['notes'] ?? 'Stock received.',
                'moved_at' => now(),
            ]);
        });

        return redirect()
            ->route('business.stock.index', [
                'business' => $business->slug,
                'branch_id' => $branch->id,
            ])
            ->with('success', 'Stock received successfully.');
    }

    public function adjust(Request $request, Business $business)
    {
        abort_unless($request->user()->can('inventory.adjust'), 403);

        $business = $this->currentBusiness($request);

        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
            'stock_batch_id' => ['required', 'integer'],
            'quantity' => ['required', 'numeric', 'gt:0', 'regex:/^\\d+(?:\\.\\d{1,4})?$/'],
            'reason' => ['required', 'in:expired,damaged,disposed,adjustment'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $branch = $this->accessibleBranches($request, $business)
            ->whereKey($validated['branch_id'])
            ->firstOrFail();

        DB::transaction(function () use ($validated, $business, $branch, $request) {
            $batch = StockBatch::query()
                ->where('business_id', $business->id)
                ->where('branch_id', $branch->id)
                ->whereKey($validated['stock_batch_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $inventory = BranchInventory::query()
                ->where('business_id', $business->id)
                ->where('branch_id', $branch->id)
                ->where('product_variation_id', $batch->product_variation_id)
                ->lockForUpdate()
                ->firstOrFail();

            $quantity = round((float) $validated['quantity'], 4);
            $batchQuantity = round((float) $batch->quantity, 4);
            $inventoryQuantity = round((float) $inventory->quantity, 4);

            if ($quantity > $batchQuantity || $quantity > $inventoryQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'The adjustment quantity exceeds the available batch or branch stock.',
                ]);
            }

            if ($validated['reason'] === 'expired' &&
                (!$batch->expiry_date || !$batch->expiry_date->lt(today()))) {
                throw ValidationException::withMessages([
                    'reason' => 'Only a batch with an expiry date before today can be written off as expired.',
                ]);
            }

            $batch->quantity = round($batchQuantity - $quantity, 4);
            $batch->save();

            $inventory->quantity = round($inventoryQuantity - $quantity, 4);
            $inventory->save();

            $movementType = in_array($validated['reason'], ['expired', 'damaged'], true)
                ? $validated['reason']
                : 'adjustment';

            $reasonLabel = match ($validated['reason']) {
                'expired' => 'Expired stock write-off',
                'damaged' => 'Damaged stock write-off',
                'disposed' => 'Disposed stock write-off',
                default => 'General stock adjustment',
            };

            StockMovement::create([
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'product_variation_id' => $batch->product_variation_id,
                'stock_batch_id' => $batch->id,
                'user_id' => $request->user()->id,
                'movement_type' => $movementType,
                'direction' => 'out',
                'quantity' => $quantity,
                'reference_type' => StockBatch::class,
                'reference_id' => $batch->id,
                'reference_number' => $batch->batch_number,
                'notes' => trim($reasonLabel . '. ' . ($validated['notes'] ?? '')),
                'moved_at' => now(),
            ]);
        });

        return redirect()
            ->route('business.stock.index', [
                'business' => $business->slug,
                'branch_id' => $branch->id,
            ])
            ->with('success', 'Stock adjustment recorded successfully.');
    }
}
