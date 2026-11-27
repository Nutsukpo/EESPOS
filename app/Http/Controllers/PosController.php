<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\StockMovement;
use App\Models\SalePayment;
use App\Models\SaleItem;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\BranchInventory;
use App\Models\StockBatch;
use App\Models\SaleItemStockBatch;

class PosController extends Controller
{
    /**
     * Display the point-of-sale screen.
     */
    public function index(Request $request): View
    {
        $business = $request->attributes->get('current_business');
        $user = $request->user();

        abort_unless($business, 403);

        $isOwner = (int) $business->owner_id === (int) $user->id;

        $branches = $business->branches()
            ->where('is_active', true)
            ->when(
                !$isOwner,
                fn ($query) => $query->whereIn(
                    'id',
                    $user->branches()
                        ->where('branches.business_id', $business->id)
                        ->where('branches.is_active', true)
                        ->select('branches.id')
                )
            )
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        abort_if($branches->isEmpty(), 403, 'You are not assigned to an active branch.');

        $selectedBranch = $branches->firstWhere(
            'id',
            $request->integer('branch_id')
        ) ?? $branches->first();

        // Keep POS lock state separate for each signed-in user and business.
        $posSessionKey = 'pos_security.' . $business->id . '.' . $user->id;
        $posSecurity = session()->get($posSessionKey);

        if (!is_array($posSecurity)) {
            $posSecurity = [
                'locked' => false,
                'last_activity_at' => now()->timestamp,
            ];
        } else {
            $lastActivity = (int) ($posSecurity['last_activity_at'] ?? 0);
            if ($lastActivity <= 0 || (now()->timestamp - $lastActivity) >= 900) {
                $posSecurity['locked'] = true;
            }
        }

        session()->put($posSessionKey, $posSecurity);

        return view('pos.index', [
            'business' => $business,
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
            'posLocked' => (bool) ($posSecurity['locked'] ?? false),
        ]);
    }

    /**
     * Search products by name, SKU or barcode for the selected branch.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $business = $request->attributes->get('current_business');
        $user = $request->user();

        abort_unless($business, 403);

        $branch = $this->authorizedBranch(
            $business,
            $user,
            (int) $validated['branch_id']
        );

        $search = trim($validated['search'] ?? '');

        if ($search === '') {
            return response()->json(['products' => []]);
        }

        $variations = ProductVariation::query()
            ->with([
                'product:id,name,image,category_id',
                'product.category:id,name',
                'barcodes:id,product_variation_id,barcode,is_primary',
            ])
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->whereHas('product', function ($query) use ($business) {
                $query->where('business_id', $business->id)
                    ->where('is_active', true);
            })
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('barcodes', function ($barcodeQuery) use ($search) {
                        $barcodeQuery->where('barcode', $search);
                    });
            })
            ->orderBy('name')
            ->limit(30)
            ->get();

        $sellableStock = StockBatch::query()
            ->where('business_id', $business->id)
            ->where('branch_id', $branch->id)
            ->whereIn('product_variation_id', $variations->pluck('id'))
            ->where('quantity', '>', 0)
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', now()->toDateString());
            })
            ->selectRaw('product_variation_id, SUM(quantity) as available_quantity')
            ->groupBy('product_variation_id')
            ->get()
            ->keyBy('product_variation_id');

        $products = $variations->map(function ($variation) use ($sellableStock) {
            $availableQuantity = (float) ($sellableStock->get($variation->id)?->available_quantity ?? 0);

            return [
                'variation_id' => $variation->id,
                'product_name' => $variation->product->name,
                'variation_name' => $variation->name,
                'sku' => $variation->sku,
                'barcode' => $variation->barcodes->firstWhere('is_primary', true)?->barcode
                    ?? $variation->barcodes->first()?->barcode,
                'category' => $variation->product->category?->name,
                'image' => $variation->product->image,
                'selling_price' => (float) $variation->selling_price,
                'tax_status' => $variation->tax_status,
                'tax_rate' => (float) $variation->tax_rate,
                'tax_inclusive' => (bool) $variation->tax_inclusive,
                'available_quantity' => $availableQuantity,
                'in_stock' => $availableQuantity > 0,
            ];
        });

        return response()->json([
            'products' => $products,
        ]);
    }


    /**
     * Complete a POS sale, record payments and deduct branch stock atomically.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
            'offline_id' => ['nullable', 'uuid'],
            'customer_id' => ['nullable', 'integer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.variation_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,mobile_money,card,split'],
            'amount_received' => ['required_if:payment_method,cash', 'nullable', 'numeric', 'min:0'],
            'payments' => ['required_if:payment_method,split', 'array', 'min:2'],
            'payments.*.method' => ['required_with:payments', 'in:cash,mobile_money,card,bank_transfer,other'],
            'payments.*.amount' => ['required_with:payments', 'numeric', 'gt:0'],
            'payments.*.reference' => ['nullable', 'string', 'max:100'],
            'payments.*.provider' => ['nullable', 'string', 'max:100'],
        ]);

        $business = $request->attributes->get('current_business');
        $user = $request->user();

        abort_unless($business, 403);

        $branch = $this->authorizedBranch(
            $business,
            $user,
            (int) $validated['branch_id']
        );

        $discount = round((float) ($validated['discount_amount'] ?? 0), 4);

        if ($discount > 0 && !$user->can('sales.discount')) {
            abort(403, 'You do not have permission to apply sale discounts.');
        }

        $customer = null;

        if (!empty($validated['customer_id'])) {
            $customer = Customer::query()
                ->where('business_id', $business->id)
                ->where('is_active', true)
                ->find($validated['customer_id']);

            if (!$customer) {
                throw ValidationException::withMessages([
                    'customer_id' => 'The selected customer is not available for this business.',
                ]);
            }
        }

        $items = collect($validated['items']);
        $variationIds = $items->pluck('variation_id')->map(fn ($id) => (int) $id)->sort()->values();

        return DB::transaction(function () use (
            $validated,
            $business,
            $user,
            $branch,
            $customer,
            $discount,
            $variationIds
        ) {
            $offlineId = $validated['offline_id'] ?? null;

            if ($offlineId) {
                $existingSale = Sale::query()
                    ->where('business_id', $business->id)
                    ->where('offline_id', $offlineId)
                    ->first();

                if ($existingSale) {
                    return response()->json([
                        'message' => 'This sale was already synchronized.',
                        'already_synced' => true,
                        'sale_id' => $existingSale->id,
                        'receipt_number' => $existingSale->receipt_number,
                        'total' => (float) $existingSale->total_amount,
                        'change' => (float) $existingSale->change_amount,
                    ]);
                }
            }

            $variations = ProductVariation::query()
                ->with('product')
                ->where('business_id', $business->id)
                ->where('is_active', true)
                ->whereIn('id', $variationIds)
                ->whereHas('product', function ($query) use ($business) {
                    $query->where('business_id', $business->id)
                        ->where('is_active', true);
                })
                ->get()
                ->keyBy('id');

            if ($variations->count() !== $variationIds->count()) {
                throw ValidationException::withMessages([
                    'items' => 'One or more selected products are no longer available.',
                ]);
            }

            $inventories = BranchInventory::query()
                ->where('business_id', $business->id)
                ->where('branch_id', $branch->id)
                ->whereIn('product_variation_id', $variationIds)
                ->orderBy('product_variation_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('product_variation_id');

            // Lock all relevant batches in a consistent order for safe FEFO allocation.
            $batchesByVariation = StockBatch::query()
                ->where('business_id', $business->id)
                ->where('branch_id', $branch->id)
                ->whereIn('product_variation_id', $variationIds)
                ->orderBy('product_variation_id')
                ->orderByRaw('expiry_date IS NULL')
                ->orderBy('expiry_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->groupBy('product_variation_id');

            $prepared = [];
            $subtotal = 0;

            foreach ($validated['items'] as $item) {
                $variationId = (int) $item['variation_id'];
                $variation = $variations->get($variationId);
                $inventory = $inventories->get($variationId);
                $quantity = round((float) $item['quantity'], 4);

                if (!$inventory || (float) $inventory->quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient branch stock for {$variation->product->name} ({$variation->name}).",
                    ]);
                }

                // Allocate from unexpired batches first, earliest expiry first.
                // Unknown expiry dates are allowed but ordered after dated batches.
                $remainingQuantity = $quantity;
                $batchAllocations = [];

                foreach ($batchesByVariation->get($variationId, collect()) as $batch) {
                    if (
                        $batch->expiry_date !== null
                        && $batch->expiry_date->toDateString() < now()->toDateString()
                    ) {
                        continue;
                    }

                    $batchAvailable = (float) $batch->quantity;

                    if ($batchAvailable <= 0 || $remainingQuantity <= 0) {
                        continue;
                    }

                    $allocatedQuantity = round(
                        min($batchAvailable, $remainingQuantity),
                        4
                    );

                    $batchAllocations[] = [
                        'batch' => $batch,
                        'quantity' => $allocatedQuantity,
                    ];

                    $remainingQuantity = round(
                        $remainingQuantity - $allocatedQuantity,
                        4
                    );
                }

                if ($remainingQuantity > 0) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient unexpired batch stock for {$variation->product->name} ({$variation->name}). Expired stock cannot be sold.",
                    ]);
                }

                $gross = round((float) $variation->selling_price * $quantity, 4);
                $subtotal += $gross;

                $prepared[] = [
                    'variation' => $variation,
                    'inventory' => $inventory,
                    'quantity' => $quantity,
                    'gross' => $gross,
                    'batch_allocations' => $batchAllocations,
                ];
            }

            $subtotal = round($subtotal, 4);

            if ($discount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'The discount cannot exceed the sale subtotal.',
                ]);
            }

            $remainingDiscount = $discount;
            $taxTotal = 0;
            $saleTotal = 0;
            $lastIndex = count($prepared) - 1;

            foreach ($prepared as $index => &$line) {
                $lineDiscount = $index === $lastIndex
                    ? $remainingDiscount
                    : ($subtotal > 0
                        ? round($discount * ($line['gross'] / $subtotal), 4)
                        : 0);

                $remainingDiscount = round($remainingDiscount - $lineDiscount, 4);
                $discounted = round($line['gross'] - $lineDiscount, 4);
                $variation = $line['variation'];
                $rate = (float) $variation->tax_rate / 100;
                $lineTax = 0;

                if ($variation->tax_status === 'taxable' && $rate > 0) {
                    $lineTax = $variation->tax_inclusive
                        ? $discounted - ($discounted / (1 + $rate))
                        : $discounted * $rate;
                }

                $lineTax = round($lineTax, 4);
                $lineTotal = $variation->tax_status === 'taxable'
                    && $rate > 0
                    && !$variation->tax_inclusive
                        ? round($discounted + $lineTax, 4)
                        : $discounted;

                $line['discount'] = $lineDiscount;
                $line['tax'] = $lineTax;
                $line['line_total'] = $lineTotal;

                $taxTotal += $lineTax;
                $saleTotal += $lineTotal;
            }
            unset($line);

            $taxTotal = round($taxTotal, 4);
            $saleTotal = round($saleTotal, 4);

            $paymentRows = [];
            $change = 0;

            if ($validated['payment_method'] === 'cash') {
                $received = round((float) $validated['amount_received'], 4);

                if ($received < $saleTotal) {
                    throw ValidationException::withMessages([
                        'amount_received' => 'The amount received is less than the sale total.',
                    ]);
                }

                $change = round($received - $saleTotal, 4);
                $paymentRows[] = [
                    'method' => 'cash',
                    'amount' => $saleTotal,
                    'reference' => null,
                    'provider' => null,
                ];
            } elseif ($validated['payment_method'] === 'split') {
                $paymentSum = 0;

                foreach ($validated['payments'] as $payment) {
                    $amount = round((float) $payment['amount'], 4);
                    $paymentSum += $amount;

                    $paymentRows[] = [
                        'method' => $payment['method'],
                        'amount' => $amount,
                        'reference' => $payment['reference'] ?? null,
                        'provider' => $payment['provider'] ?? null,
                    ];
                }

                if (abs(round($paymentSum, 4) - $saleTotal) > 0.0001) {
                    throw ValidationException::withMessages([
                        'payments' => 'Split payment amounts must add up exactly to the sale total.',
                    ]);
                }
            } else {
                $paymentRows[] = [
                    'method' => $validated['payment_method'],
                    'amount' => $saleTotal,
                    'reference' => null,
                    'provider' => null,
                ];
            }

            $receiptNumber = 'POS-' . now()->format('Ymd-His') . '-' . Str::upper(Str::random(6));

            $sale = Sale::create([
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer?->id,
                'cashier_id' => $user->id,
                'receipt_number' => $receiptNumber,
                'offline_id' => $offlineId,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $taxTotal,
                'total_amount' => $saleTotal,
                'amount_paid' => $saleTotal,
                'change_amount' => $change,
                'status' => 'completed',
                'payment_status' => 'paid',
                'sold_at' => now(),
            ]);

            foreach ($prepared as $line) {
                $variation = $line['variation'];

                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $variation->product_id,
                    'product_variation_id' => $variation->id,
                    'product_name' => $variation->product->name,
                    'variation_name' => $variation->name,
                    'sku' => $variation->sku,
                    'quantity' => $line['quantity'],
                    'unit_price' => $variation->selling_price,
                    'cost_price' => $variation->cost_price,
                    'discount_amount' => $line['discount'],
                    'tax_rate' => $variation->tax_rate,
                    'tax_amount' => $line['tax'],
                    'line_total' => $line['line_total'],
                ]);

                foreach ($line['batch_allocations'] as $allocation) {
                    $batch = $allocation['batch'];
                    $allocatedQuantity = $allocation['quantity'];

                    $batch->quantity = round(
                        (float) $batch->quantity - $allocatedQuantity,
                        4
                    );
                    $batch->save();

                    SaleItemStockBatch::create([
                        'sale_item_id' => $saleItem->id,
                        'stock_batch_id' => $batch->id,
                        'quantity' => $allocatedQuantity,
                    ]);

                    StockMovement::create([
                        'business_id' => $business->id,
                        'branch_id' => $branch->id,
                        'product_variation_id' => $variation->id,
                        'stock_batch_id' => $batch->id,
                        'user_id' => $user->id,
                        'movement_type' => 'sale',
                        'direction' => 'out',
                        'quantity' => $allocatedQuantity,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'reference_number' => $receiptNumber,
                        'notes' => 'POS sale - batch ' . ($batch->batch_number ?? $batch->id),
                        'moved_at' => now(),
                    ]);
                }

                $line['inventory']->quantity = round(
                    (float) $line['inventory']->quantity - $line['quantity'],
                    4
                );
                $line['inventory']->save();

            }

            foreach ($paymentRows as $payment) {
                SalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_method' => $payment['method'],
                    'amount' => $payment['amount'],
                    'reference' => $payment['reference'],
                    'provider' => $payment['provider'],
                    'status' => 'completed',
                    'paid_at' => now(),
                    'received_by' => $user->id,
                ]);
            }

            return response()->json([
                'message' => 'Sale completed successfully.',
                'sale_id' => $sale->id,
                'receipt_number' => $receiptNumber,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $taxTotal,
                'total' => $saleTotal,
                'amount_paid' => $saleTotal,
                'change' => $change,
            ], 201);
        });
    }

    /**
     * Ensure the user can operate the selected branch.
     */
    private function authorizedBranch($business, $user, int $branchId): Branch
    {
        $isOwner = (int) $business->owner_id === (int) $user->id;

        $branchQuery = $business->branches()
            ->where('is_active', true)
            ->whereKey($branchId);

        if (!$isOwner) {
            $assigned = $user->branches()
                ->where('branches.business_id', $business->id)
                ->where('branches.is_active', true)
                ->whereKey($branchId)
                ->exists();

            abort_unless($assigned, 403, 'You are not assigned to this branch.');
        }

        return $branchQuery->firstOrFail();
    }
}
