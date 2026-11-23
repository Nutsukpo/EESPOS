<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    private function currentBusiness(Request $request): Business
    {
        $business = $request->attributes->get('current_business');

        abort_unless($business instanceof Business, 403);

        return $business;
    }

    public function index(Request $request, Business $business)
    {
        abort_unless($request->user()->can('products.view'), 403);

        $branches = Branch::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $selectedBranch = $branches->firstWhere(
            'id',
            (int) $request->query('branch_id')
        ) ?? $branches->firstWhere('is_main', true) ?? $branches->first();

        $query = Product::query()
            ->where('business_id', $business->id)
            ->with(['category', 'variations'])
            ->withCount('variations');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhereHas('variations', function ($variation) use ($search) {
                        $variation->where('sku', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('type') &&
            in_array($request->type, ['simple', 'variable'], true)) {
            $query->where('product_type', $request->type);
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        $variationIds = $products->getCollection()
            ->flatMap(fn ($product) => $product->variations->pluck('id'))
            ->unique()
            ->values();

        $inventories = collect();
        $openingStocks = collect();
        $expiryBatches = collect();

        if ($selectedBranch && $variationIds->isNotEmpty()) {
            $expiryBatches = StockBatch::where('business_id', $business->id)
                ->where('branch_id', $selectedBranch->id)
                ->whereIn('product_variation_id', $variationIds)
                ->where('quantity', '>', 0)
                ->whereNotNull('expiry_date')
                ->orderBy('expiry_date')
                ->get()
                ->groupBy('product_variation_id');

            $inventories = BranchInventory::where('business_id', $business->id)
                ->where('branch_id', $selectedBranch->id)
                ->whereIn('product_variation_id', $variationIds)
                ->get()
                ->keyBy('product_variation_id');

            $openingStocks = StockMovement::where('business_id', $business->id)
                ->where('branch_id', $selectedBranch->id)
                ->whereIn('product_variation_id', $variationIds)
                ->where('movement_type', 'opening_stock')
                ->where('direction', 'in')
                ->selectRaw('product_variation_id, SUM(quantity) as total')
                ->groupBy('product_variation_id')
                ->pluck('total', 'product_variation_id');
        }

        foreach ($products as $product) {
            $productVariationIds = $product->variations->pluck('id');

            $product->setAttribute(
                'opening_stock',
                $productVariationIds->sum(
                    fn ($id) => (float) ($openingStocks[$id] ?? 0)
                )
            );

            $product->setAttribute(
                'current_stock',
                $productVariationIds->sum(
                    fn ($id) => (float) ($inventories->get($id)?->quantity ?? 0)
                )
            );

            $product->setAttribute(
                'minimum_stock',
                $productVariationIds->sum(
                    fn ($id) => (float) ($inventories->get($id)?->minimum_stock ?? 0)
                )
            );

            $nearestExpiryBatch = $productVariationIds
                ->flatMap(fn ($id) => $expiryBatches->get($id, collect()))
                ->sortBy('expiry_date')
                ->first();

            $product->setAttribute(
                'nearest_expiry_date',
                $nearestExpiryBatch?->expiry_date
            );
        }

        $categories = ProductCategory::where('business_id', $business->id)
            ->orderBy('name')
            ->get();

        $statistics = [
            'total' => Product::where('business_id', $business->id)->count(),
            'active' => Product::where('business_id', $business->id)
                ->where('is_active', true)->count(),
            'inactive' => Product::where('business_id', $business->id)
                ->where('is_active', false)->count(),
            'variable' => Product::where('business_id', $business->id)
                ->where('product_type', 'variable')->count(),
        ];

        return view('businesses.products.index', compact(
            'business',
            'products',
            'categories',
            'statistics',
            'branches',
            'selectedBranch'
        ));
    }

    public function create(Request $request, Business $business)
    {
        abort_unless($request->user()->can('products.create'), 403);

        $categories = ProductCategory::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = Branch::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        return view('businesses.products.create', compact(
            'business',
            'categories',
            'branches'
        ));
    }

    public function store(Request $request, Business $business)
    {
        abort_unless($request->user()->can('products.create'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'nullable',
                Rule::exists('product_categories', 'id')
                    ->where('business_id', $business->id)
                    ->where('is_active', true),
            ],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:255'],
            'product_type' => ['required', Rule::in(['simple', 'variable'])],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'branch_id' => [
                'required',
                Rule::exists('branches', 'id')
                    ->where('business_id', $business->id)
                    ->where('is_active', true),
            ],

            'variations' => ['required', 'array', 'min:1'],
            'variations.*.name' => ['required', 'string', 'max:255'],
            'variations.*.sku' => [
                'required',
                'string',
                'max:255',
                'distinct',
                Rule::unique('product_variations', 'sku')
                    ->where('business_id', $business->id),
            ],
            'variations.*.cost_price' => ['required', 'numeric', 'min:0'],
            'variations.*.selling_price' => ['required', 'numeric', 'min:0'],
            'variations.*.opening_quantity' => ['required', 'numeric', 'min:0'],
            'variations.*.expiry_date' => ['nullable', 'date'],
            'variations.*.minimum_stock' => ['required', 'numeric', 'min:0'],
            'variations.*.tax_status' => [
                'required',
                Rule::in(['taxable', 'exempt', 'zero_rated']),
            ],
            'variations.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'variations.*.tax_inclusive' => ['nullable', 'boolean'],
        ]);

        if ($validated['product_type'] === 'simple' &&
            count($validated['variations']) !== 1) {
            throw ValidationException::withMessages([
                'variations' => 'A simple product must have exactly one variation.',
            ]);
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('products/' . $business->id, 'public')
            : null;

        DB::transaction(function () use ($validated, $business, $imagePath, $request) {
            $product = Product::create([
                'business_id' => $business->id,
                'category_id' => $validated['category_id'] ?? null,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'product_type' => $validated['product_type'],
                'is_active' => $validated['is_active'],
                'image' => $imagePath,
            ]);

            foreach ($validated['variations'] as $variationData) {
                $variation = $product->variations()->create([
                    'business_id' => $business->id,
                    'name' => $variationData['name'],
                    'sku' => trim($variationData['sku']),
                    'cost_price' => $variationData['cost_price'],
                    'selling_price' => $variationData['selling_price'],
                    'tax_status' => $variationData['tax_status'],
                    'tax_rate' => $variationData['tax_rate'] ?? 0,
                    'tax_inclusive' => $variationData['tax_inclusive'] ?? false,
                    'is_active' => true,
                ]);

                $openingQuantity = (float) $variationData['opening_quantity'];

                BranchInventory::create([
                    'business_id' => $business->id,
                    'branch_id' => $validated['branch_id'],
                    'product_variation_id' => $variation->id,
                    'quantity' => $openingQuantity,
                    'minimum_stock' => $variationData['minimum_stock'],
                    'last_stocked_at' => $openingQuantity > 0 ? now() : null,
                ]);

                if ($openingQuantity > 0) {
                    $batch = StockBatch::create([
                        'business_id' => $business->id,
                        'branch_id' => $validated['branch_id'],
                        'product_variation_id' => $variation->id,
                        'batch_number' => null,
                        'expiry_date' => $variationData['expiry_date'] ?? null,
                        'received_quantity' => $openingQuantity,
                        'quantity' => $openingQuantity,
                        'received_at' => now(),
                        'created_by' => $request->user()->id,
                        'notes' => 'Opening stock recorded during product creation.',
                    ]);

                    StockMovement::create([
                        'business_id' => $business->id,
                        'branch_id' => $validated['branch_id'],
                        'product_variation_id' => $variation->id,
                        'stock_batch_id' => $batch->id,
                        'user_id' => $request->user()->id,
                        'movement_type' => 'opening_stock',
                        'direction' => 'in',
                        'quantity' => $openingQuantity,
                        'reference_type' => 'stock_batch',
                        'reference_id' => $batch->id,
                        'reference_number' => $batch->batch_number,
                        'notes' => 'Opening stock recorded during product creation.',
                        'moved_at' => now(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('business.products.index', ['business' => $business->slug])
            ->with('success', 'Product created successfully.');
    }


    public function show(Request $request, Business $business, int $product)
    {
        abort_unless($request->user()->can('products.view'), 403);

        $product = Product::where('business_id', $business->id)
            ->with([
                'category',
                'variations.inventories.branch',
                'variations.stockMovements' => fn ($query) => $query
                    ->with(['branch', 'user'])
                    ->latest('moved_at'),
            ])
            ->findOrFail($product);

        return view('businesses.products.show', compact('business', 'product'));
    }

    public function edit(Request $request, Business $business, int $product)
    {
        abort_unless($request->user()->can('products.edit'), 403);

        $product = Product::where('business_id', $business->id)
            ->with('variations')
            ->findOrFail($product);

        $categories = ProductCategory::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('businesses.products.edit', compact(
            'business',
            'product',
            'categories'
        ));
    }

    public function update(Request $request, Business $business, int $product)
    {
        abort_unless($request->user()->can('products.edit'), 403);

        $product = Product::where('business_id', $business->id)
            ->with('variations')
            ->findOrFail($product);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'nullable',
                Rule::exists('product_categories', 'id')
                    ->where('business_id', $business->id)
                    ->where('is_active', true),
            ],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:255'],
            'product_type' => ['required', Rule::in(['simple', 'variable'])],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'variations' => ['required', 'array', 'min:1'],
            'variations.*.id' => ['nullable', 'integer'],
            'variations.*.name' => ['required', 'string', 'max:255'],
            'variations.*.sku' => ['required', 'string', 'max:255', 'distinct'],
            'variations.*.cost_price' => ['required', 'numeric', 'min:0'],
            'variations.*.selling_price' => ['required', 'numeric', 'min:0'],
            'variations.*.tax_status' => [
                'required',
                Rule::in(['taxable', 'exempt', 'zero_rated']),
            ],
            'variations.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'variations.*.tax_inclusive' => ['nullable', 'boolean'],
        ]);

        if ($validated['product_type'] === 'simple' &&
            count($validated['variations']) !== 1) {
            throw ValidationException::withMessages([
                'variations' => 'A simple product must have exactly one variation.',
            ]);
        }

        $existingIds = $product->variations->pluck('id')->map(fn ($id) => (int) $id);
        $submittedIds = collect($validated['variations'])
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id);

        if ($existingIds->diff($submittedIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'variations' => 'Existing variations cannot be removed here because they may have stock history.',
            ]);
        }

        foreach ($validated['variations'] as $variationData) {
            $variationId = $variationData['id'] ?? null;

            if ($variationId && !$existingIds->contains((int) $variationId)) {
                throw ValidationException::withMessages([
                    'variations' => 'One of the selected variations does not belong to this product.',
                ]);
            }

            $skuRule = Rule::unique('product_variations', 'sku')
                ->where('business_id', $business->id);

            if ($variationId) {
                $skuRule->ignore($variationId);
            }

            $validator = validator(
                ['sku' => $variationData['sku']],
                ['sku' => [$skuRule]]
            );

            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    'variations' => 'Each variation SKU must be unique within this business.',
                ]);
            }
        }

        $oldImagePath = $product->image;

        $newImagePath = $request->hasFile('image')
            ? $request->file('image')->store('products/' . $business->id, 'public')
            : null;

        DB::transaction(function () use (
            $validated,
            $business,
            $product,
            $newImagePath
        ) {
            $product->update([
                'category_id' => $validated['category_id'] ?? null,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'brand' => $validated['brand'] ?? null,
                'product_type' => $validated['product_type'],
                'is_active' => $validated['is_active'],
                'image' => $newImagePath ?? $product->image,
            ]);

            foreach ($validated['variations'] as $variationData) {
                $variationValues = [
                    'name' => $variationData['name'],
                    'sku' => trim($variationData['sku']),
                    'cost_price' => $variationData['cost_price'],
                    'selling_price' => $variationData['selling_price'],
                    'tax_status' => $variationData['tax_status'],
                    'tax_rate' => $variationData['tax_rate'] ?? 0,
                    'tax_inclusive' => $variationData['tax_inclusive'] ?? false,
                ];

                if (!empty($variationData['id'])) {
                    $product->variations()
                        ->where('business_id', $business->id)
                        ->whereKey($variationData['id'])
                        ->firstOrFail()
                        ->update($variationValues);
                } else {
                    $variation = $product->variations()->create(array_merge(
                        $variationValues,
                        [
                            'business_id' => $business->id,
                            'is_active' => true,
                        ]
                    ));

                    $branches = Branch::where('business_id', $business->id)
                        ->where('is_active', true)
                        ->get();

                    foreach ($branches as $branch) {
                        BranchInventory::create([
                            'business_id' => $business->id,
                            'branch_id' => $branch->id,
                            'product_variation_id' => $variation->id,
                            'quantity' => 0,
                            'minimum_stock' => 0,
                        ]);
                    }
                }
            }
        });

        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()
            ->route('business.products.index', ['business' => $business->slug])
            ->with('success', 'Product updated successfully. Stock quantities were not changed.');
    }

    public function destroy(Request $request, Business $business, int $product)
    {
        abort_unless($request->user()->can('products.delete'), 403);

        $product = Product::where('business_id', $business->id)
            ->with('variations')
            ->findOrFail($product);

        DB::transaction(function () use ($product) {
            foreach ($product->variations as $variation) {
                $variation->delete();
            }

            $product->delete();
        });

        return redirect()
            ->route('business.products.index', ['business' => $business->slug])
            ->with('success', 'Product deleted. Inventory and stock movement history have been preserved.');
    }

}
