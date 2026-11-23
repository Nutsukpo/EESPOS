@extends('layouts.app')

@section('title', 'Category Details')

@section('styles')
<style>
    .category-detail-page { max-width:1200px; margin:auto; }
    .detail-heading { display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap; margin-bottom:22px; }
    .detail-heading h2 { font-size:1.35rem; font-weight:800; margin:0; color:var(--text); }
    .detail-heading p { color:var(--muted); font-size:.8rem; margin:5px 0 0; }
    .detail-panel { background:#fff; border:1px solid var(--border); border-radius:15px; padding:22px; margin-bottom:18px; }
    .detail-label { color:#89968e; font-size:.68rem; text-transform:uppercase; letter-spacing:.05em; font-weight:700; }
    .detail-value { color:#1b3326; font-size:.9rem; font-weight:650; margin-top:5px; }
    .detail-section-title { font-size:.95rem; font-weight:800; color:var(--text); margin-bottom:16px; }
    .detail-table { width:100%; border-collapse:collapse; }
    .detail-table th { background:#f7faf8; color:#718078; font-size:.66rem; text-transform:uppercase; padding:12px; text-align:left; }
    .detail-table td { padding:12px; border-top:1px solid #edf1ee; font-size:.77rem; color:#34483c; }
    .status-pill { display:inline-block; border-radius:20px; padding:5px 9px; font-size:.65rem; font-weight:700; }
    .status-active { background:#e8f6ed; color:#19834a; }
    .status-inactive { background:#fff0ed; color:#bd4536; }
    .detail-actions { display:flex; gap:8px; flex-wrap:wrap; }
    body.dark-mode .detail-panel { background:#111b2d; border-color:#26344a; }
    body.dark-mode .detail-value { color:#f1f5f2; }
    body.dark-mode .detail-table th { background:#172033; }
    body.dark-mode .detail-table td { border-color:#26344a; color:#d5e0d8; }
    @media(max-width:767px) { .detail-table-wrap { overflow-x:auto; } .detail-table { min-width:550px; } }
</style>
@endsection

@section('content')
<div class="category-detail-page">

    <div class="detail-heading">
        <div>
            <h2><i class="fas fa-tags me-2" style="color:#28634a"></i>Category Details</h2>
            <p>View category information, subcategories and assigned products.</p>
        </div>

        <div class="detail-actions">
            <a href="{{ route('business.categories.index', ['business' => $business->slug]) }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>

            @can('categories.edit')
                <a href="{{ route('business.categories.edit', ['business' => $business->slug, 'category' => $category->id]) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> Edit Category
                </a>
            @endcan
        </div>
    </div>

    <div class="detail-panel">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="detail-label">Category Name</div>
                <div class="detail-value">{{ $category->name }}</div>
            </div>

            <div class="col-md-6">
                <div class="detail-label">Parent Category</div>
                <div class="detail-value">{{ $category->parent?->name ?? 'Main Category' }}</div>
            </div>

            <div class="col-md-6">
                <div class="detail-label">Status</div>
                <div class="mt-2">
                    <span class="status-pill {{ $category->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>

            <div class="col-md-6">
                <div class="detail-label">Date Created</div>
                <div class="detail-value">{{ $category->created_at?->format('d M Y, h:i A') }}</div>
            </div>

            <div class="col-12">
                <div class="detail-label">Description</div>
                <div class="detail-value" style="font-weight:400; white-space:pre-line;">
                    {{ $category->description ?: 'No description provided.' }}
                </div>
            </div>
        </div>
    </div>

    <div class="detail-panel">
        <div class="detail-section-title">
            Subcategories ({{ $category->children->count() }})
        </div>

        @if($category->children->isNotEmpty())
            <div class="detail-table-wrap">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->children as $child)
                            <tr>
                                <td>
                                    @can('categories.view')
                                        <a href="{{ route('business.categories.show', ['business' => $business->slug, 'category' => $child->id]) }}">
                                            {{ $child->name }}
                                        </a>
                                    @else
                                        {{ $child->name }}
                                    @endcan
                                </td>
                                <td>{{ $child->is_active ? 'Active' : 'Inactive' }}</td>
                                <td>{{ $child->created_at?->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted small mb-0">No subcategories have been added.</p>
        @endif
    </div>

    <div class="detail-panel">
        <div class="detail-section-title">
            Assigned Products ({{ $category->products->count() }})
        </div>

        @if($category->products->isNotEmpty())
            <div class="detail-table-wrap">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Brand</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->brand ?: '—' }}</td>
                                <td>{{ ucfirst($product->product_type) }}</td>
                                <td>{{ $product->is_active ? 'Active' : 'Inactive' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted small mb-0">No products are currently assigned to this category.</p>
        @endif
    </div>

</div>
@endsection
