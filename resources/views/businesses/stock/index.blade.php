@extends('layouts.app')

@section('title', 'Stock Management')

@section('styles')
<style>
    .stock-page { max-width:1500px; margin:auto; }
    .stock-heading { display:flex; justify-content:space-between; align-items:center; gap:15px; flex-wrap:wrap; margin-bottom:22px; }
    .stock-heading h2 { font-size:1.35rem; font-weight:800; margin:0; color:var(--text); }
    .stock-heading p { color:var(--muted); font-size:.8rem; margin:5px 0 0; }
    .stock-panel { background:#fff; border:1px solid var(--border); border-radius:15px; overflow:hidden; margin-bottom:20px; }
    .stock-panel-header { padding:16px 18px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
    .stock-panel-header h3 { margin:0; font-size:.95rem; font-weight:800; color:#1b3326; }
    .stock-panel-body { padding:18px; }
    .stock-form-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
    .stock-field label { display:block; font-size:.7rem; font-weight:700; color:#52645a; margin-bottom:6px; }
    .stock-field input,.stock-field select,.branch-select { width:100%; min-height:41px; border:1px solid #dfe8e2; border-radius:9px; padding:8px 11px; font-size:.77rem; background:#fff; color:#263b30; }
    .stock-field-full { grid-column:1/-1; }
    .stock-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:11px 17px; border:0; border-radius:10px; background:#173b2d; color:#fff; font-size:.78rem; font-weight:700; }
    .stock-btn:hover { background:#28634a; color:#fff; }
    .stock-table-wrap { overflow-x:auto; }
    .stock-table { width:100%; border-collapse:collapse; }
    .stock-table th { background:#f7faf8; color:#718078; font-size:.66rem; text-transform:uppercase; letter-spacing:.05em; padding:13px 15px; white-space:nowrap; text-align:left; }
    .stock-table td { padding:13px 15px; border-top:1px solid #edf1ee; font-size:.76rem; vertical-align:top; color:#34483c; }
    .stock-name { font-weight:750; color:#1b3326; }
    .stock-muted { color:#89968e; font-size:.68rem; margin-top:3px; }
    .stock-pill { display:inline-block; border-radius:20px; padding:5px 9px; font-size:.65rem; font-weight:700; white-space:nowrap; }
    .stock-good { background:#e8f6ed; color:#19834a; }
    .stock-warning { background:#fff6df; color:#9a6a00; }
    .stock-danger { background:#fff0ed; color:#bd4536; }
    .stock-neutral { background:#eef3f0; color:#52645a; }
    .batch-list { display:grid; gap:7px; min-width:230px; }
    .batch-row { display:flex; justify-content:space-between; gap:12px; align-items:center; padding:8px 10px; background:#f7faf8; border-radius:8px; }
    .batch-row strong { font-size:.7rem; color:#34483c; }
    .batch-row small { display:block; color:#89968e; font-size:.65rem; margin-top:2px; }
    .stock-empty { padding:38px 20px; text-align:center; color:#718078; }
    .stock-empty i { font-size:1.8rem; color:#b5c5ba; margin-bottom:10px; }
    .stock-pagination { padding:15px; }
    body.dark-mode .stock-panel { background:#111b2d; border-color:#26344a; }
    body.dark-mode .stock-panel-header { border-color:#26344a; }
    body.dark-mode .stock-panel-header h3, body.dark-mode .stock-name { color:#f1f5f2; }
    body.dark-mode .stock-table th { background:#172033; }
    body.dark-mode .stock-table td { border-color:#26344a; color:#d5e0d8; }
    body.dark-mode .batch-row { background:#172033; }
    body.dark-mode .batch-row strong { color:#d5e0d8; }
    @media(max-width:767px) {
        .stock-form-grid { grid-template-columns:1fr; }
        .stock-field-full { grid-column:auto; }
        .stock-table { min-width:850px; }
    }
</style>
@endsection

@section('content')
<div class="stock-page">
    <div class="stock-heading">
        <div>
            <h2><i class="fas fa-warehouse me-2" style="color:#28634a"></i>Stock Management</h2>
            <p>Receive stock, track batches and monitor expiry dates by branch.</p>
        </div>

        @if($branches->isNotEmpty())
            <form method="GET" action="{{ route('business.stock.index', ['business' => $business->slug]) }}">
                <select name="branch_id" class="branch-select" onchange="this.form.submit()" aria-label="Select branch">
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected($selectedBranch?->id === $branch->id)>
                            {{ $branch->name }}{{ $branch->is_main ? ' (Main Branch)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show small" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger small">
            <strong>Please check the following:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!$selectedBranch)
        <div class="stock-panel">
            <div class="stock-empty">
                <i class="fas fa-warehouse d-block"></i>
                No active branch is available for your account.
            </div>
        </div>
    @else
        @can('inventory.adjust')
        <div class="stock-panel">
            <div class="stock-panel-header">
                <h3><i class="fas fa-circle-plus me-2" style="color:#28634a"></i>Receive New Stock</h3>
            </div>
            <div class="stock-panel-body">
                <form method="POST" action="{{ route('business.stock.receive', ['business' => $business->slug]) }}">
                    @csrf
                    <input type="hidden" name="branch_id" value="{{ $selectedBranch->id }}">

                    <div class="stock-form-grid">
                        <div class="stock-field">
                            <label for="product_variation_id">Product / Variation *</label>
                            <select id="product_variation_id" name="product_variation_id" required>
                                <option value="">Select product</option>
                                @foreach($variations as $variation)
                                    <option value="{{ $variation->id }}" @selected(old('product_variation_id') == $variation->id)>
                                        {{ $variation->product?->name ?? 'Product unavailable' }} — {{ $variation->name }}
                                        @if($variation->sku) ({{ $variation->sku }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="stock-field">
                            <label for="quantity">Quantity Received *</label>
                            <input id="quantity" type="number" name="quantity" min="0.0001" step="0.0001" value="{{ old('quantity') }}" required placeholder="Enter quantity">
                        </div>

                        <div class="stock-field">
                            <label for="batch_number">Batch Number</label>
                            <input id="batch_number" type="text" name="batch_number" maxlength="255" value="{{ old('batch_number') }}" placeholder="Supplier batch number">
                        </div>

                        <div class="stock-field">
                            <label for="expiry_date">Expiry Date</label>
                            <input id="expiry_date" type="date" name="expiry_date" min="{{ now()->toDateString() }}" value="{{ old('expiry_date') }}">
                        </div>

                        <div class="stock-field stock-field-full">
                            <label for="notes">Notes</label>
                            <input id="notes" type="text" name="notes" maxlength="2000" value="{{ old('notes') }}" placeholder="Supplier, delivery reference or other notes">
                        </div>

                        <div class="stock-field stock-field-full">
                            <button type="submit" class="stock-btn">
                                <i class="fas fa-plus"></i> Receive Stock
                            </button>
                            <small class="stock-muted d-block mt-2">Enter the expiry date when the product has one. Existing stock with unknown expiry dates remains unchanged.</small>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endcan

        <div class="stock-panel">
            <div class="stock-panel-header">
                <h3><i class="fas fa-calendar-exclamation me-2" style="color:#bd4536"></i>Expiry Monitoring</h3>
                <span class="stock-pill stock-neutral">Next {{ $expiryDays }} days</span>
            </div>

            <div class="stock-panel-body">
                <div class="row g-3 mb-3">
                    <div class="col-6 col-lg-3">
                        <div class="product-stat">
                            <small>Expired Batches</small>
                            <strong style="color:#bd4536">{{ number_format($expirySummary['expired_count']) }}</strong>
                            <div class="stock-muted">{{ number_format($expirySummary['expired_quantity'], 4) }} units remaining</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="product-stat">
                            <small>Near-expiry Batches</small>
                            <strong style="color:#9a6a00">{{ number_format($expirySummary['near_count']) }}</strong>
                            <div class="stock-muted">{{ number_format($expirySummary['near_quantity'], 4) }} units remaining</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="stock-muted me-1">Expiry window:</span>
                    @foreach([7, 30, 60, 90] as $days)
                        <a href="{{ route('business.stock.index', ['business' => $business->slug, 'branch_id' => $selectedBranch->id, 'expiry_days' => $days]) }}"
                           class="stock-pill {{ $expiryDays === $days ? 'stock-good' : 'stock-neutral' }}"
                           style="text-decoration:none">
                            {{ $days }} days
                        </a>
                    @endforeach
                </div>

                @if($expirySummary['expired_count'] > 0)
                    <div class="alert alert-danger py-2 small">
                        <i class="fas fa-triangle-exclamation me-2"></i>
                        <strong>Expired stock alert:</strong>
                        {{ number_format($expirySummary['expired_quantity'], 4) }} units across
                        {{ $expirySummary['expired_count'] }} batches must not be sold.
                        Record a batch adjustment to write them off.
                    </div>
                @endif

                @php
                    $expiredReportBatches = $expiryBatches->filter(
                        fn ($batch) => $batch->expiry_date->lt(today())
                    );

                    $nearReportBatches = $expiryBatches->filter(
                        fn ($batch) => $batch->expiry_date->gte(today())
                            && $batch->expiry_date->lte(today()->addDays($expiryDays))
                    );
                @endphp

                @if($expiredReportBatches->isNotEmpty() || $nearReportBatches->isNotEmpty())
                    <div class="stock-table-wrap">
                        <table class="stock-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Batch Number</th>
                                    <th>Expiry Date</th>
                                    <th>Days</th>
                                    <th>Remaining Quantity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expiredReportBatches->concat($nearReportBatches) as $batch)
                                    @php
                                        $daysRemaining = (int) today()->diffInDays($batch->expiry_date, false);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="stock-name">{{ $batch->productVariation?->product?->name ?? 'Unavailable product' }}</div>
                                            <div class="stock-muted">{{ $batch->productVariation?->name ?? 'Variation unavailable' }}</div>
                                        </td>
                                        <td>{{ $batch->batch_number ?: 'Batch #' . $batch->id }}</td>
                                        <td>{{ $batch->expiry_date->format('d M Y') }}</td>
                                        <td>
                                            @if($daysRemaining < 0)
                                                {{ abs($daysRemaining) }} days overdue
                                            @else
                                                {{ $daysRemaining }} days
                                            @endif
                                        </td>
                                        <td>{{ number_format((float) $batch->quantity, 4) }}</td>
                                        <td>
                                            @if($daysRemaining < 0)
                                                <span class="stock-pill stock-danger">Expired</span>
                                            @else
                                                <span class="stock-pill stock-warning">Near expiry</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="stock-empty">
                        <i class="fas fa-shield-check d-block"></i>
                        No expired or near-expiry batches found in this reporting window.
                    </div>
                @endif
            </div>
        </div>

        <div class="stock-panel">
            <div class="stock-panel-header">
                <h3><i class="fas fa-boxes-stacked me-2" style="color:#28634a"></i>Branch Stock & Batches</h3>
                <span class="stock-pill stock-neutral">{{ $selectedBranch->name }}</span>
            </div>

            @if($inventories->isEmpty())
                <div class="stock-empty">
                    <i class="fas fa-box-open d-block"></i>
                    No inventory records found for this branch.
                </div>
            @else
                <div class="stock-table-wrap">
                    <table class="stock-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Total Stock</th>
                                <th>Batch Details (FEFO order)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventories as $inventory)
                                @php
                                    $variation = $inventory->productVariation;
                                    $product = $variation?->product;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="stock-name">{{ $product?->name ?? 'Unavailable product' }}</div>
                                        <div class="stock-muted">
                                            {{ $variation?->name ?? 'Variation unavailable' }}
                                            @if($variation?->sku) · SKU: {{ $variation->sku }} @endif
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ number_format((float) $inventory->quantity, 4) }}</strong>
                                        <div class="stock-muted">Minimum: {{ number_format((float) $inventory->minimum_stock, 4) }}</div>
                                    </td>
                                    <td>
                                        @if($inventory->batches->isEmpty())
                                            <span class="stock-pill stock-neutral">No batch records</span>
                                        @else
                                            <div class="batch-list">
                                                @foreach($inventory->batches as $batch)
                                                    @php
                                                        $isExpired = $batch->expiry_date && $batch->expiry_date->lt(today());
                                                        $daysToExpiry = $batch->expiry_date ? today()->diffInDays($batch->expiry_date, false) : null;
                                                    @endphp
                                                    <div class="batch-row">
                                                        <div>
                                                            <strong>{{ $batch->batch_number ?: 'Batch #' . $batch->id }}</strong>
                                                            <small>
                                                                @if($batch->expiry_date)
                                                                    Expires {{ $batch->expiry_date->format('d M Y') }}
                                                                @else
                                                                    Expiry date not recorded
                                                                @endif
                                                                · Qty {{ number_format((float) $batch->quantity, 4) }}
                                                            </small>
                                                        </div>
                                                        @if($isExpired)
                                                            <span class="stock-pill stock-danger">Expired</span>
                                                        @elseif($batch->expiry_date && $daysToExpiry <= 30)
                                                            <span class="stock-pill stock-warning">Near expiry</span>
                                                        @elseif($batch->expiry_date)
                                                            <span class="stock-pill stock-good">Valid</span>
                                                        @else
                                                            <span class="stock-pill stock-neutral">Unknown expiry</span>
                                                        @endif
                                                    </div>

                                                    @can('inventory.adjust')
                                                        @if((float) $batch->quantity > 0)
                                                            <form method="POST"
                                                                  action="{{ route('business.stock.adjust', ['business' => $business->slug]) }}"
                                                                  class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                                                @csrf
                                                                <input type="hidden" name="branch_id" value="{{ $selectedBranch->id }}">
                                                                <input type="hidden" name="stock_batch_id" value="{{ $batch->id }}">

                                                                <input type="number"
                                                                       name="quantity"
                                                                       min="0.0001"
                                                                       max="{{ $batch->quantity }}"
                                                                       step="0.0001"
                                                                       required
                                                                       placeholder="Qty"
                                                                       aria-label="Adjustment quantity"
                                                                       style="width:95px;min-height:34px;border:1px solid #dfe8e2;border-radius:7px;padding:5px 8px;font-size:.7rem;">

                                                                <select name="reason"
                                                                        required
                                                                        aria-label="Adjustment reason"
                                                                        style="min-height:34px;border:1px solid #dfe8e2;border-radius:7px;padding:5px 8px;font-size:.7rem;">
                                                                    <option value="">Reason</option>
                                                                    <option value="expired">Expired</option>
                                                                    <option value="damaged">Damaged</option>
                                                                    <option value="disposed">Disposed</option>
                                                                    <option value="adjustment">General correction</option>
                                                                </select>

                                                                <input type="text"
                                                                       name="notes"
                                                                       maxlength="2000"
                                                                       placeholder="Notes (optional)"
                                                                       aria-label="Adjustment notes"
                                                                       style="width:145px;min-height:34px;border:1px solid #dfe8e2;border-radius:7px;padding:5px 8px;font-size:.7rem;">

                                                                <button type="submit"
                                                                        class="btn btn-sm btn-outline-danger"
                                                                        style="font-size:.68rem;">
                                                                    Record adjustment
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(method_exists($inventories, 'links'))
                    <div class="stock-pagination">{{ $inventories->links() }}</div>
                @endif
            @endif
        </div>
    @endif
</div>
@endsection
