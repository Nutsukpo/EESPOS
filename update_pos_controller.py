from pathlib import Path

path = Path("app/Http/Controllers/PosController.php")
content = path.read_text()

if "public function index(" in content:
    raise SystemExit("POS controller already contains index(). No changes made.")

new_content = r'''<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

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

        return view('pos.index', [
            'business' => $business,
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
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

        $stock = $branch->inventories()
            ->whereIn('product_variation_id', $variations->pluck('id'))
            ->get()
            ->keyBy('product_variation_id');

        $products = $variations->map(function ($variation) use ($stock) {
            $inventory = $stock->get($variation->id);

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
                'available_quantity' => (float) ($inventory?->quantity ?? 0),
                'in_stock' => $inventory && (float) $inventory->quantity > 0,
            ];
        });

        return response()->json([
            'products' => $products,
        ]);
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
'''

path.write_text(new_content)
print("Successfully created the initial POS controller.")
