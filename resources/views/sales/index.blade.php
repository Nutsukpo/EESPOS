@extends('layouts.app')

@section('title', 'Sales History')
@section('page-heading', 'Sales History')

@php
    $currency = $business->currency ?? 'GHS';
    $currencySymbols = ['GHS' => '₵', 'USD' => '$', 'GBP' => '£', 'EUR' => '€'];
    $currencySymbol = $currencySymbols[$currency] ?? $currency;
    $filteredTotal = $sales->getCollection()->sum(fn ($sale) => (float) $sale->total_amount);
@endphp

@push('styles')
<style>
    .sales-page { max-width: 1600px; margin: 0 auto; padding: 20px; color: #14261c; }
    .sales-header, .sales-panel, .sales-stat {
        background: #fff; border: 1px solid #e2e9e4; border-radius: 14px;
        box-shadow: 0 2px 8px rgba(16,43,32,.04);
    }
    .sales-header { padding: 22px; display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; margin-bottom:18px; }
    .sales-header h2 { margin:0 0 5px; font-size:1.25rem; font-weight:800; }
    .sales-muted { color:#718078; font-size:.88rem; margin:0; }
    .sales-stat { padding:18px 20px; margin-bottom:18px; }
    .sales-stat span { display:block; color:#718078; font-size:.85rem; margin-bottom:7px; }
    .sales-stat strong { font-size:1.45rem; color:#1a5c3f; }
    .sales-panel { padding:20px; }
    .sales-filters { display:grid; grid-template-columns: minmax(200px,2fr) repeat(5,minmax(130px,1fr)) auto; gap:12px; align-items:end; }
    .sales-field label { display:block; font-size:.8rem; font-weight:700; color:#46594e; margin-bottom:6px; }
    .sales-field input, .sales-field select {
        width:100%; min-height:42px; border:1px solid #dce5df; border-radius:8px;
        padding:8px 10px; background:#fff; color:#24372c;
    }
    .sales-button { display:inline-flex; align-items:center; justify-content:center; gap:7px; min-height:42px; padding:9px 15px; border:0; border-radius:8px; background:#1a5c3f; color:#fff; font-weight:700; text-decoration:none; cursor:pointer; }
    .sales-button:hover { background:#237a54; color:#fff; }
    .sales-reset { color:#52645a; text-decoration:none; font-size:.85rem; display:inline-block; margin-top:12px; }
    .sales-table-wrap { overflow-x:auto; margin-top:20px; }
    .sales-table { width:100%; border-collapse:collapse; min-width:850px; }
    .sales-table th { text-align:left; padding:13px 12px; color:#718078; background:#f7faf8; font-size:.76rem; text-transform:uppercase; letter-spacing:.04em; }
    .sales-table td { padding:14px 12px; border-bottom:1px solid #edf1ee; font-size:.88rem; vertical-align:middle; }
    .sales-table tbody tr:hover { background:#fbfdfb; }
    .sales-receipt { color:#1a5c3f; font-weight:800; text-decoration:none; }
    .sales-badge { display:inline-block; border-radius:20px; padding:5px 9px; font-size:.74rem; font-weight:700; background:#eef2ef; color:#52645a; text-transform:capitalize; }
    .sales-badge.completed, .sales-badge.paid { background:#e6f5ed; color:#187044; }
    .sales-badge.unpaid, .sales-badge.voided, .sales-badge.failed { background:#fbeceb; color:#a53e36; }
    .sales-empty { text-align:center; padding:45px 15px; color:#718078; }
    .sales-pagination { margin-top:18px; }
    @media(max-width:1100px) { .sales-filters { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media(max-width:650px) { .sales-page { padding:12px; } .sales-filters { grid-template-columns:1fr 1fr; } .sales-panel { padding:14px; } }
</style>
@endpush

@section('content')
<div class="sales-page">
    <div class="sales-header">
        <div>
            <h2><i class="fas fa-receipt" style="color:#1a5c3f"></i> Sales History</h2>
            <p class="sales-muted">Review completed and previous transactions for {{ $business->name }}.</p>
        </div>
        @can('sales.create')
            <a class="sales-button" href="{{ route('business.pos.index', ['business' => $business->slug]) }}">
                <i class="fas fa-cash-register"></i> Open POS
            </a>
        @endcan
    </div>

    <div class="sales-stat">
        <span>Filtered sales total · {{ $sales->total() }} matching records</span>
        <strong>{{ $currencySymbol }}{{ number_format($filteredTotal, 2) }}</strong>
        <p class="sales-muted" style="margin-top:5px">Total for the records on this page.</p>
    </div>

    <div class="sales-panel">
        <form method="GET" action="{{ route('business.sales.index', ['business' => $business->slug]) }}">
            <div class="sales-filters">
                <div class="sales-field">
                    <label for="search">Search sales</label>
                    <input id="search" name="search" value="{{ request('search') }}" placeholder="Receipt, customer, cashier">
                </div>
                <div class="sales-field">
                    <label for="branch_id">Branch</label>
                    <select id="branch_id" name="branch_id">
                        <option value="">All accessible branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sales-field">
                    <label for="status">Sale status</label>
                    <select id="status" name="status">
                        <option value="">All statuses</option>
                        @foreach(['completed', 'draft', 'voided', 'refunded', 'partially_refunded'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sales-field">
                    <label for="payment_status">Payment</label>
                    <select id="payment_status" name="payment_status">
                        <option value="">All payments</option>
                        @foreach(['paid', 'unpaid', 'partially_paid', 'refunded'] as $status)
                            <option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sales-field">
                    <label for="date_from">From date</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="sales-field">
                    <label for="date_to">To date</label>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>
                <button class="sales-button" type="submit"><i class="fas fa-filter"></i> Filter</button>
            </div>
            <a class="sales-reset" href="{{ route('business.sales.index', ['business' => $business->slug]) }}">Clear all filters</a>
        </form>

        <div class="sales-table-wrap">
            <table class="sales-table">
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Branch</th>
                        <th>Cashier</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td><a class="sales-receipt" href="{{ route('business.sales.show', ['business' => $business->slug, 'sale' => $sale->id]) }}">{{ $sale->receipt_number }}</a></td>
                            <td>{{ $sale->sold_at?->format('d M Y, h:i A') ?? $sale->created_at?->format('d M Y, h:i A') }}</td>
                            <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td>{{ $sale->branch?->name ?? '—' }}</td>
                            <td>{{ $sale->cashier?->name ?? '—' }}</td>
                            <td><strong>{{ $currencySymbol }}{{ number_format((float) $sale->total_amount, 2) }}</strong></td>
                            <td><span class="sales-badge {{ $sale->payment_status }}">{{ ucwords(str_replace('_', ' ', $sale->payment_status)) }}</span></td>
                            <td><span class="sales-badge {{ $sale->status }}">{{ ucwords(str_replace('_', ' ', $sale->status)) }}</span></td>
                            <td><a class="sales-receipt" href="{{ route('business.sales.show', ['business' => $business->slug, 'sale' => $sale->id]) }}">View <i class="fas fa-arrow-right"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="sales-empty"><i class="fas fa-receipt" style="font-size:2rem;display:block;margin-bottom:12px"></i>No sales found for these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="sales-pagination">{{ $sales->links() }}</div>
    </div>
</div>
@endsection
