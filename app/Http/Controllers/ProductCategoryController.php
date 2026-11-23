<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductCategoryController extends Controller
{
    public function index(Request $request, Business $business)
    {
        abort_unless($request->user()->can('categories.view'), 403);

        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $query = ProductCategory::query()
            ->where('business_id', $business->id)
            ->with('parent')
            ->withCount(['products', 'children']);

        if ($request->filled('search')) {
            $search = trim($request->query('search'));

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->query('status') === 'active'
            );
        }

        $categories = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $totalCategories = ProductCategory::where(
            'business_id',
            $business->id
        )->count();

        $activeCategories = ProductCategory::where(
            'business_id',
            $business->id
        )->where('is_active', true)->count();

        $inactiveCategories = $totalCategories - $activeCategories;

        return view('businesses.categories.index', compact(
            'business',
            'categories',
            'totalCategories',
            'activeCategories',
            'inactiveCategories'
        ));
    }

    public function create(Request $request, Business $business)
    {
        abort_unless($request->user()->can('categories.create'), 403);

        $parentCategories = ProductCategory::where(
            'business_id',
            $business->id
        )->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('businesses.categories.create', compact(
            'business',
            'parentCategories'
        ));
    }

    public function store(Request $request, Business $business)
    {
        abort_unless($request->user()->can('categories.create'), 403);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_categories', 'name')
                    ->where('business_id', $business->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('product_categories', 'id')
                    ->where('business_id', $business->id),
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        ProductCategory::create([
            'business_id' => $business->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        return redirect()
            ->route('business.categories.index', [
                'business' => $business->slug,
            ])
            ->with('success', 'Category created successfully.');
    }

    public function show(
        Request $request,
        Business $business,
        int $category
    ) {
        abort_unless($request->user()->can('categories.view'), 403);

        $category = ProductCategory::where('business_id', $business->id)
            ->with([
                'parent',
                'children' => fn ($query) => $query->orderBy('name'),
                'products' => fn ($query) => $query->orderBy('name'),
            ])
            ->withCount(['products', 'children'])
            ->findOrFail($category);

        return view('businesses.categories.show', compact(
            'business',
            'category'
        ));
    }

    public function edit(
        Request $request,
        Business $business,
        int $category
    ) {
        abort_unless($request->user()->can('categories.edit'), 403);

        $category = ProductCategory::where('business_id', $business->id)
            ->with('parent')
            ->findOrFail($category);

        $parentCategories = ProductCategory::where(
            'business_id',
            $business->id
        )->where('id', '!=', $category->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $excludedIds = $this->descendantIds($category);

        $parentCategories = $parentCategories
            ->reject(fn ($item) => in_array(
                $item->id,
                $excludedIds,
                true
            ))
            ->values();

        return view('businesses.categories.edit', compact(
            'business',
            'category',
            'parentCategories'
        ));
    }

    public function update(
        Request $request,
        Business $business,
        int $category
    ) {
        abort_unless($request->user()->can('categories.edit'), 403);

        $category = ProductCategory::where('business_id', $business->id)
            ->findOrFail($category);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_categories', 'name')
                    ->where('business_id', $business->id)
                    ->ignore($category->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('product_categories', 'id')
                    ->where('business_id', $business->id),
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId !== null) {
            $excludedIds = $this->descendantIds($category);
            $excludedIds[] = $category->id;

            if (in_array((int) $parentId, $excludedIds, true)) {
                throw ValidationException::withMessages([
                    'parent_id' => 'A category cannot be placed under itself or one of its subcategories.',
                ]);
            }
        }

        $category->update([
            'parent_id' => $parentId,
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        return redirect()
            ->route('business.categories.index', [
                'business' => $business->slug,
            ])
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(
        Request $request,
        Business $business,
        int $category
    ) {
        abort_unless($request->user()->can('categories.delete'), 403);

        $category = ProductCategory::where('business_id', $business->id)
            ->findOrFail($category);

        if ($category->products()->exists()) {
            return back()->with(
                'error',
                'This category cannot be deleted because products are assigned to it.'
            );
        }

        if ($category->children()->exists()) {
            return back()->with(
                'error',
                'Move or delete its subcategories before deleting this category.'
            );
        }

        $category->delete();

        return redirect()
            ->route('business.categories.index', [
                'business' => $business->slug,
            ])
            ->with('success', 'Category deleted successfully.');
    }

    private function descendantIds(ProductCategory $category): array
    {
        $ids = [];
        $pending = [$category->id];

        while (!empty($pending)) {
            $children = ProductCategory::where(
                'business_id',
                $category->business_id
            )->whereIn('parent_id', $pending)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $newIds = array_values(array_diff($children, $ids));

            if (empty($newIds)) {
                break;
            }

            $ids = array_merge($ids, $newIds);
            $pending = $newIds;
        }

        return $ids;
    }
}
