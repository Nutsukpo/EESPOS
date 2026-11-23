@extends('layouts.app')

@section('title', 'Products')

@section('styles')
<style>
    .products-page { max-width: 1500px; margin: auto; }
    .products-heading { display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap; margin-bottom:22px; }
    .products-heading h2 { font-size:1.35rem; font-weight:800; margin:0; color:var(--text); }
    .products-heading p { color:var(--muted); font-size:.8rem; margin:5px 0 0; }
    .product-btn { display:inline-flex; align-items:center; gap:8px; padding:11px 17px; border-radius:10px; background:#173b2d; color:#fff; font-size:.78rem; font-weight:700; }
    .product-btn:hover { background:#28634a; color:#fff; }
    .product-stat { background:#fff; border:1px solid var(--border); border-radius:14px; padding:17px; height:100%; }
    .product-stat small { color:var(--muted); font-size:.7rem; font-weight:600; }
    .product-stat strong { display:block; font-size:1.5rem; margin-top:7px; color:var(--text); }
    .product-panel { background:#fff; border:1px solid var(--border); border-radius:15px; overflow:hidden; }
    .product-filters { padding:17px; display:flex; gap:10px; flex-wrap:wrap; border-bottom:1px solid var(--border); }
    .product-filters input,.product-filters select { min-height:41px; border:1px solid #dfe8e2; border-radius:9px; padding:8px 11px; font-size:.77rem; background:#fff; color:#263b30; }
    .product-search { flex:1; min-width:190px; }
    .filter-btn { border:0; border-radius:9px; background:#173b2d; color:#fff; padding:9px 16px; font-size:.76rem; font-weight:700; }
    .product-table { width:100%; border-collapse:collapse; }
    .product-table th { background:#f7faf8; color:#718078; font-size:.66rem; text-transform:uppercase; letter-spacing:.05em; padding:13px 15px; white-space:nowrap; }
    .product-table td { padding:13px 15px; border-top:1px solid #edf1ee; font-size:.76rem; vertical-align:middle; color:#34483c; }
    .product-thumb { width:45px; height:45px; border-radius:9px; object-fit:cover; background:#edf3ef; }
    .product-name { font-weight:750; color:#1b3326; }
    .product-muted { color:#89968e; font-size:.68rem; margin-top:3px; }
    .product-actions form { display:inline-flex; }
    .product-table td form { margin:0; }
    .product-table td .btn { min-width:32px; }
    .status-pill { display:inline-block; border-radius:20px; padding:5px 9px; font-size:.65rem; font-weight:700; }
    .status-active { background:#e8f6ed; color:#19834a; }
    .status-inactive { background:#fff0ed; color:#bd4536; }
    .stock-in { background:#e8f6ed; color:#19834a; }
    .stock-low { background:#fff6df; color:#9a6a00; }
    .stock-out { background:#fff0ed; color:#bd4536; }
    .stock-na { background:#eef3f0; color:#52645a; }
    .expiry-upcoming { background:#e8f6ed; color:#19834a; }
    .expiry-soon { background:#fff2d6; color:#a76500; }
    .expiry-expired { background:#ffe1dc; color:#c62828; }
    .expiry-na { background:#eef3f0; color:#52645a; }
    .type-pill { background:#eef3f0; color:#52645a; border-radius:20px; padding:5px 9px; font-size:.65rem; }
    .empty-products { padding:55px 20px; text-align:center; color:#718078; }
    .empty-products i { font-size:2rem; color:#b5c5ba; margin-bottom:13px; }
    .pagination-wrap { padding:15px; }
    body.dark-mode .product-stat, body.dark-mode .product-panel { background:#111b2d; border-color:#26344a; }
    body.dark-mode .product-table th { background:#172033; }
    body.dark-mode .product-table td { border-color:#26344a; color:#d5e0d8; }
    body.dark-mode .product-name { color:#f1f5f2; }
    body.dark-mode .product-filters { border-color:#26344a; }
    @media(max-width:767px) { .product-table-wrap { overflow-x:auto; } .product-table { min-width:1250px; } }
</style>
@endsection

@section('content')
<div class="products-page">

    <div class="products-heading">
        <div>
            <h2><i class="fas fa-boxes-stacked me-2" style="color:#28634a"></i>Products</h2>
            <p>Manage products, variations, prices and product information.</p>
        </div>

        @can('products.create')
        <a href="{{ route('business.products.create', ['business' => $business->slug]) }}" class="product-btn">
            <i class="fas fa-plus"></i> Add Product
        </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show small" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="product-stat">
                <small>Total Products</small>
                <strong>{{ number_format($statistics['total']) }}</strong>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="product-stat">
                <small>Active Products</small>
                <strong>{{ number_format($statistics['active']) }}</strong>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="product-stat">
                <small>Inactive Products</small>
                <strong>{{ number_format($statistics['inactive']) }}</strong>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="product-stat">
                <small>Variable Products</small>
                <strong>{{ number_format($statistics['variable']) }}</strong>
            </div>
        </div>
    </div>

    <div class="product-panel">
        <form method="GET" action="{{ route('business.products.index', ['business' => $business->slug]) }}" class="product-filters">
            <input class="product-search" type="text" name="search" value="{{ request('search') }}" placeholder="Search product, brand or SKU...">

            <select name="branch_id">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected($selectedBranch?->id == $branch->id)>
                        {{ $branch->name }}{{ $branch->is_main ? ' (Main Branch)' : '' }}
                    </option>
                @endforeach
            </select>

            <select name="category">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <select name="type">
                <option value="">All Types</option>
                <option value="simple" @selected(request('type') === 'simple')>Simple</option>
                <option value="variable" @selected(request('type') === 'variable')>Variable</option>
            </select>

            <select name="status">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>

            <button type="submit" class="filter-btn"><i class="fas fa-filter me-1"></i> Filter</button>
            <a href="{{ route('business.products.index', ['business' => $business->slug]) }}" class="btn btn-light btn-sm d-flex align-items-center">Reset</a>
        </form>

        <div class="product-table-wrap">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Item Description</th>
                        <th>Category</th>
                        <!-- <th>Type</th> -->
                        <th>Selling Price</th>
                        <th>Opening Stock</th>
                        <th>Current Stock</th>
                        <th>Expiry Date</th>
                        <th>Minimum Stock</th>
                        <th>Stock Status</th>
                        <th>Variations</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $firstVariation = $product->variations->first();
                            $imageUrl = $product->image ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) : null;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="product-thumb">
                                    @else
                                        <div class="product-thumb d-flex align-items-center justify-content-center">
                                            <i class="fas fa-box text-secondary"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="product-name">{{ $product->name }}</div>
                                        <div class="product-muted">{{ $product->brand ?: 'No brand specified' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $firstVariation?->sku ?? '—' }}</td>
                            <td>{{ $product->category?->name ?? 'Uncategorized' }}</td>
                            <!-- <td><span class="type-pill">{{ ucfirst($product->product_type) }}</span></td> -->
                            <td>{{ $firstVariation ? number_format((float)$firstVariation->selling_price, 2) : '—' }}</td>
                            <td>{{ number_format($product->opening_stock, 2) }}</td>
                            <td><strong>{{ number_format($product->current_stock, 2) }}</strong></td>
                            <td>
                                @if(!$product->nearest_expiry_date)
                                    <span class="status-pill expiry-na">No expiry</span>
                                @elseif($product->nearest_expiry_date->lt(today()))
                                    <span class="status-pill expiry-expired">
                                        Expired · {{ $product->nearest_expiry_date->format('d M Y') }}
                                    </span>
                                @elseif($product->nearest_expiry_date->lte(today()->addDays(7)))
                                    <span class="status-pill expiry-soon">
                                        {{ $product->nearest_expiry_date->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="status-pill expiry-upcoming">
                                        {{ $product->nearest_expiry_date->format('d M Y') }}
                                    </span>
                                @endif
                            </td>
                            <td>{{ number_format($product->minimum_stock, 2) }}</td>
                            <td>
                                @if(!$selectedBranch)
                                    <span class="status-pill stock-na">No Branch</span>
                                @elseif($product->current_stock <= 0)
                                    <span class="status-pill stock-out">Out of Stock</span>
                                @elseif($product->current_stock <= $product->minimum_stock)
                                    <span class="status-pill stock-low">Low Stock</span>
                                @else
                                    <span class="status-pill stock-in">In Stock</span>
                                @endif
                            </td>
                            <td>{{ $product->variations_count }}</td>
                            <td>
                                <span class="status-pill {{ $product->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    @can('products.view')
                                        <a href="{{ route('business.products.show', ['business' => $business->slug, 'product' => $product->id]) }}"
                                           class="btn btn-sm btn-outline-success" title="View Product">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan

                                    @can('products.edit')
                                        <a href="{{ route('business.products.edit', ['business' => $business->slug, 'product' => $product->id]) }}"
                                           class="btn btn-sm btn-outline-primary" title="Edit Product">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan

                                    @can('products.delete')
                                        <form method="POST"
                                              action="{{ route('business.products.destroy', ['business' => $business->slug, 'product' => $product->id]) }}"
                                              onsubmit="return confirm('Are you sure you want to delete this product? Its inventory and stock movement history will be preserved.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Product">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13">
                                <div class="empty-products">
                                    <i class="fas fa-box-open d-block"></i>
                                    <strong>No products found</strong>
                                    <p class="small mb-3">Start by adding your first product to this business.</p>
                                    @can('products.create')
                                    <a href="{{ route('business.products.create', ['business' => $business->slug]) }}" class="product-btn">
                                        <i class="fas fa-plus"></i> Add First Product
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="pagination-wrap">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
