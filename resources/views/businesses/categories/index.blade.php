@extends('layouts.app')

@section('title', 'Product Categories')

@section('styles')
<style>
    .categories-page { max-width:1500px; margin:auto; }
    .categories-heading { display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap; margin-bottom:22px; }
    .categories-heading h2 { font-size:1.35rem; font-weight:800; margin:0; color:var(--text); }
    .categories-heading p { color:var(--muted); font-size:.8rem; margin:5px 0 0; }
    .category-btn { display:inline-flex; align-items:center; gap:8px; padding:11px 17px; border-radius:10px; background:#173b2d; color:#fff; font-size:.78rem; font-weight:700; text-decoration:none; }
    .category-btn:hover { background:#28634a; color:#fff; }
    .category-stat { background:#fff; border:1px solid var(--border); border-radius:14px; padding:17px; height:100%; }
    .category-stat small { color:var(--muted); font-size:.7rem; font-weight:600; }
    .category-stat strong { display:block; font-size:1.5rem; margin-top:7px; color:var(--text); }
    .category-panel { background:#fff; border:1px solid var(--border); border-radius:15px; overflow:hidden; }
    .category-filters { padding:17px; display:flex; gap:10px; flex-wrap:wrap; border-bottom:1px solid var(--border); }
    .category-filters input,.category-filters select { min-height:41px; border:1px solid #dfe8e2; border-radius:9px; padding:8px 11px; font-size:.77rem; background:#fff; color:#263b30; }
    .category-search { flex:1; min-width:190px; }
    .filter-btn { border:0; border-radius:9px; background:#173b2d; color:#fff; padding:9px 16px; font-size:.76rem; font-weight:700; }
    .category-table { width:100%; border-collapse:collapse; }
    .category-table th { background:#f7faf8; color:#718078; font-size:.66rem; text-transform:uppercase; letter-spacing:.05em; padding:13px 15px; white-space:nowrap; }
    .category-table td { padding:13px 15px; border-top:1px solid #edf1ee; font-size:.76rem; vertical-align:middle; color:#34483c; }
    .category-name { font-weight:750; color:#1b3326; }
    .category-muted { color:#89968e; font-size:.68rem; margin-top:3px; }
    .category-actions form { display:inline-flex; margin:0; }
    .category-table td .btn { min-width:32px; }
    .status-pill { display:inline-block; border-radius:20px; padding:5px 9px; font-size:.65rem; font-weight:700; }
    .status-active { background:#e8f6ed; color:#19834a; }
    .status-inactive { background:#fff0ed; color:#bd4536; }
    .empty-categories { padding:55px 20px; text-align:center; color:#718078; }
    .empty-categories i { font-size:2rem; color:#b5c5ba; margin-bottom:13px; }
    .pagination-wrap { padding:15px; }
    body.dark-mode .category-stat, body.dark-mode .category-panel { background:#111b2d; border-color:#26344a; }
    body.dark-mode .category-table th { background:#172033; }
    body.dark-mode .category-table td { border-color:#26344a; color:#d5e0d8; }
    body.dark-mode .category-name { color:#f1f5f2; }
    body.dark-mode .category-filters { border-color:#26344a; }
    @media(max-width:767px) { .category-table-wrap { overflow-x:auto; } .category-table { min-width:850px; } }
</style>
@endsection

@section('content')
<div class="categories-page">

    <div class="categories-heading">
        <div>
            <h2><i class="fas fa-tags me-2" style="color:#28634a"></i>Product Categories</h2>
            <p>Organize products into categories and subcategories.</p>
        </div>

        @can('categories.create')
            <a href="{{ route('business.categories.create', ['business' => $business->slug]) }}" class="category-btn">
                <i class="fas fa-plus"></i> Add Category
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show small" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show small" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger small">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="category-stat">
                <small>Total Categories</small>
                <strong>{{ number_format($totalCategories) }}</strong>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="category-stat">
                <small>Active Categories</small>
                <strong>{{ number_format($activeCategories) }}</strong>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="category-stat">
                <small>Inactive Categories</small>
                <strong>{{ number_format($inactiveCategories) }}</strong>
            </div>
        </div>
    </div>

    <div class="category-panel">
        <form method="GET" action="{{ route('business.categories.index', ['business' => $business->slug]) }}" class="category-filters">
            <input class="category-search" type="text" name="search" value="{{ request('search') }}" placeholder="Search category or description...">

            <select name="status">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>

            <button type="submit" class="filter-btn">
                <i class="fas fa-filter me-1"></i> Filter
            </button>

            <a href="{{ route('business.categories.index', ['business' => $business->slug]) }}" class="btn btn-light btn-sm d-flex align-items-center">
                Reset
            </a>
        </form>

        <div class="category-table-wrap">
            <table class="category-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Parent Category</th>
                        <th>Subcategories</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>
                                <div class="category-name">{{ $category->name }}</div>
                                <div class="category-muted">
                                    {{ \Illuminate\Support\Str::limit($category->description ?: 'No description', 65) }}
                                </div>
                            </td>

                            <td>{{ $category->parent?->name ?? 'Main Category' }}</td>

                            <td>{{ number_format($category->children_count) }}</td>

                            <td>{{ number_format($category->products_count) }}</td>

                            <td>
                                <span class="status-pill {{ $category->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>{{ $category->created_at?->format('d M Y') }}</td>

                            <td>
                                <div class="category-actions d-flex align-items-center gap-1">
                                    @can('categories.view')
                                        <a href="{{ route('business.categories.show', ['business' => $business->slug, 'category' => $category->id]) }}"
                                           class="btn btn-sm btn-outline-success" title="View Category">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan

                                    @can('categories.edit')
                                        <a href="{{ route('business.categories.edit', ['business' => $business->slug, 'category' => $category->id]) }}"
                                           class="btn btn-sm btn-outline-primary" title="Edit Category">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan

                                    @can('categories.delete')
                                        <form method="POST"
                                              action="{{ route('business.categories.destroy', ['business' => $business->slug, 'category' => $category->id]) }}"
                                              onsubmit="return confirm('Delete this category? Categories containing products or subcategories cannot be deleted.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Category">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-categories">
                                    <i class="fas fa-tags d-block"></i>
                                    <strong>No categories found</strong>
                                    <p class="small mb-3">Create your first category to start organizing products.</p>

                                    @can('categories.create')
                                        <a href="{{ route('business.categories.create', ['business' => $business->slug]) }}" class="category-btn">
                                            <i class="fas fa-plus"></i> Add First Category
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="pagination-wrap">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
