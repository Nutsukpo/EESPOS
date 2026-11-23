@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $product->name }}</h2>
            <p class="text-muted mb-0">Product details and inventory information</p>
        </div>
        <div class="d-flex gap-2">
            @can('products.edit')
                <a href="{{ route('business.products.edit', ['business' => $business->slug, 'product' => $product->id]) }}"
                   class="btn btn-success">Edit Product</a>
            @endcan
            <a href="{{ route('business.products.index', ['business' => $business->slug]) }}"
               class="btn btn-outline-secondary">Back to Products</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-4">
                @if($product->image)
                    <div class="col-md-3">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}"
                             alt="{{ $product->name }}"
                             class="img-fluid rounded"
                             style="max-height:220px;object-fit:contain;">
                    </div>
                @endif
                <div class="col-md">
                    <h4>{{ $product->name }}</h4>
                    <p class="text-muted mb-2">{{ $product->description ?: 'No description provided.' }}</p>
                    <div class="row g-3 mt-2">
                        <div class="col-sm-6 col-lg-3">
                            <small class="text-muted d-block">Brand</small>
                            <strong>{{ $product->brand ?: '—' }}</strong>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <small class="text-muted d-block">Category</small>
                            <strong>{{ $product->category?->name ?? 'Uncategorized' }}</strong>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <small class="text-muted d-block">Product Type</small>
                            <strong>{{ ucfirst($product->product_type) }}</strong>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <small class="text-muted d-block">Status</small>
                            <strong>{{ $product->is_active ? 'Active' : 'Inactive' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Product Variations and Branch Stock</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Variation</th>
                        <th>SKU</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Tax</th>
                        <th>Branch</th>
                        <th>Current Stock</th>
                        <th>Minimum Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($product->variations as $variation)
                        @forelse($variation->inventories as $inventory)
                            <tr>
                                <td>{{ $variation->name }}</td>
                                <td>{{ $variation->sku }}</td>
                                <td>{{ number_format((float) $variation->cost_price, 2) }}</td>
                                <td>{{ number_format((float) $variation->selling_price, 2) }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $variation->tax_status)) }} ({{ number_format((float) $variation->tax_rate, 2) }}%)</td>
                                <td>{{ $inventory->branch?->name ?? '—' }}</td>
                                <td><strong>{{ number_format((float) $inventory->quantity, 2) }}</strong></td>
                                <td>{{ number_format((float) $inventory->minimum_stock, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td>{{ $variation->name }}</td>
                                <td>{{ $variation->sku }}</td>
                                <td>{{ number_format((float) $variation->cost_price, 2) }}</td>
                                <td>{{ number_format((float) $variation->selling_price, 2) }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $variation->tax_status)) }}</td>
                                <td colspan="3" class="text-muted">No branch inventory record</td>
                            </tr>
                        @endforelse
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Recent Stock Movements</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Variation</th>
                        <th>Branch</th>
                        <th>Movement</th>
                        <th>Direction</th>
                        <th>Quantity</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $movements = $product->variations
                            ->flatMap(fn ($variation) => $variation->stockMovements)
                            ->sortByDesc('moved_at')
                            ->take(30);
                    @endphp

                    @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->moved_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td>{{ $movement->productVariation?->name ?? '—' }}</td>
                            <td>{{ $movement->branch?->name ?? '—' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $movement->movement_type)) }}</td>
                            <td>{{ ucfirst($movement->direction) }}</td>
                            <td>{{ number_format((float) $movement->quantity, 2) }}</td>
                            <td>{{ $movement->user?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No stock movements recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
