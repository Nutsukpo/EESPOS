@extends('layouts.app')

@section('title', 'Sale Details')
@section('page-heading', 'Sale Details')

@php
    $currency = $business->currency ?? 'GHS';
    $currencySymbols = ['GHS' => '₵', 'USD' => '$', 'GBP' => '£', 'EUR' => '€'];
    $currencySymbol = $currencySymbols[$currency] ?? $currency;
@endphp

@push('styles')
<style>
    .sale-detail-page { max-width:1100px; margin:0 auto; padding:20px; color:#14261c; }
    .sale-detail-panel { background:#fff; border:1px solid #e2e9e4; border-radius:14px; padding:22px; margin-bottom:18px; box-shadow:0 2px 8px rgba(16,43,32,.04); }
    .sale-detail-header { display:flex; justify-content:space-between; align-items:center; gap:14px; flex-wrap:wrap; margin-bottom:18px; }
    .sale-detail-header h2 { margin:0 0 5px; font-size:1.25rem; font-weight:800; }
    .sale-detail-muted { color:#718078; font-size:.88rem; }
    .sale-detail-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
    .sale-detail-label { color:#718078; font-size:.78rem; margin-bottom:5px; }
    .sale-detail-value { font-weight:700; overflow-wrap:anywhere; }
    .sale-detail-table { width:100%; border-collapse:collapse; min-width:580px; }
    .sale-detail-table th { background:#f7faf8; color:#718078; text-align:left; font-size:.76rem; text-transform:uppercase; padding:12px; }
    .sale-detail-table td { padding:13px 12px; border-bottom:1px solid #edf1ee; font-size:.88rem; }
    .sale-detail-table-wrap { overflow-x:auto; }
    .sale-detail-total { display:flex; justify-content:space-between; gap:20px; padding:8px 0; color:#52645a; }
    .sale-detail-total.grand { border-top:1px solid #dce5df; margin-top:8px; padding-top:16px; color:#14261c; font-size:1.1rem; font-weight:800; }
    .sale-detail-button { display:inline-flex; align-items:center; gap:8px; padding:10px 15px; background:#1a5c3f; color:#fff; border-radius:8px; text-decoration:none; border:0; cursor:pointer; font-weight:700; }
    .sale-detail-button:hover { background:#237a54; color:#fff; }
    .sale-detail-badge { display:inline-block; padding:5px 10px; border-radius:20px; background:#e6f5ed; color:#187044; font-size:.78rem; font-weight:700; text-transform:capitalize; }
    @media(max-width:650px) { .sale-detail-page{padding:12px}.sale-detail-panel{padding:15px}.sale-detail-grid{grid-template-columns:1fr 1fr} }
    @media print {
        @page { margin:10mm; }
        body * { visibility:hidden!important; }
        .sale-print-area, .sale-print-area * { visibility:visible!important; }
        .sale-print-area { position:absolute!important; left:0!important; top:0!important; width:100%!important; box-shadow:none!important; border:0!important; }
        .sale-no-print { display:none!important; }
    }
</style>
@endpush

@section('content')
<div class="sale-detail-page">
    <div class="sale-detail-header sale-no-print">
        <div>
            <h2><i class="fas fa-receipt" style="color:#1a5c3f"></i> {{ $sale->receipt_number }}</h2>
            <p class="sale-detail-muted">Sale recorded {{ $sale->sold_at?->format('d M Y, h:i A') ?? $sale->created_at?->format('d M Y, h:i A') }}</p>
        </div>
        <div style="display:flex;gap:9px;flex-wrap:wrap">
            <a class="sale-detail-button" href="{{ route('business.sales.index', ['business' => $business->slug]) }}"><i class="fas fa-arrow-left"></i> Sales History</a>
            <button class="sale-detail-button" type="button" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>

    <div class="sale-detail-panel sale-print-area">
        <div style="text-align:center;padding-bottom:18px;border-bottom:1px dashed #cfdad3;margin-bottom:20px">
            <h2 style="margin:0 0 5px">{{ $business->name }}</h2>
            <div class="sale-detail-muted">{{ $sale->branch?->name }}</div>
            <strong style="display:block;margin-top:12px">SALES RECEIPT</strong>
            <div class="sale-detail-muted">{{ $sale->receipt_number }}</div>
        </div>

        <div class="sale-detail-grid" style="margin-bottom:22px">
            <div><div class="sale-detail-label">Date</div><div class="sale-detail-value">{{ $sale->sold_at?->format('d M Y, h:i A') ?? '—' }}</div></div>
            <div><div class="sale-detail-label">Customer</div><div class="sale-detail-value">{{ $sale->customer?->name ?? 'Walk-in customer' }}</div></div>
            <div><div class="sale-detail-label">Cashier</div><div class="sale-detail-value">{{ $sale->cashier?->name ?? '—' }}</div></div>
            <div><div class="sale-detail-label">Sale status</div><div><span class="sale-detail-badge">{{ ucwords(str_replace('_', ' ', $sale->status)) }}</span></div></div>
            <div><div class="sale-detail-label">Payment status</div><div><span class="sale-detail-badge">{{ ucwords(str_replace('_', ' ', $sale->payment_status)) }}</span></div></div>
            <div><div class="sale-detail-label">Customer phone</div><div class="sale-detail-value">{{ $sale->customer?->phone ?? '—' }}</div></div>
        </div>

        <div class="sale-detail-table-wrap">
            <table class="sale-detail-table">
                <thead><tr><th>Item</th><th>SKU</th><th>Qty</th><th>Unit price</th><th>Discount</th><th>Total</th></tr></thead>
                <tbody>
                    @foreach($sale->items as $item)
                        <tr>
                            <td><strong>{{ $item->product_name }}</strong>@if($item->variation_name)<div class="sale-detail-muted">{{ $item->variation_name }}</div>@endif</td>
                            <td>{{ $item->sku ?? '—' }}</td>
                            <td>{{ number_format((float)$item->quantity, 2) }}</td>
                            <td>{{ $currencySymbol }}{{ number_format((float)$item->unit_price, 2) }}</td>
                            <td>{{ $currencySymbol }}{{ number_format((float)$item->discount_amount, 2) }}</td>
                            <td><strong>{{ $currencySymbol }}{{ number_format((float)$item->line_total, 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="max-width:390px;margin:22px 0 0 auto">
            <div class="sale-detail-total"><span>Subtotal</span><span>{{ $currencySymbol }}{{ number_format((float)$sale->subtotal, 2) }}</span></div>
            <div class="sale-detail-total"><span>Discount</span><span>{{ $currencySymbol }}{{ number_format((float)$sale->discount_amount, 2) }}</span></div>
            <div class="sale-detail-total"><span>Tax</span><span>{{ $currencySymbol }}{{ number_format((float)$sale->tax_amount, 2) }}</span></div>
            <div class="sale-detail-total grand"><span>Total</span><span>{{ $currencySymbol }}{{ number_format((float)$sale->total_amount, 2) }}</span></div>
            <div class="sale-detail-total"><span>Amount paid</span><span>{{ $currencySymbol }}{{ number_format((float)$sale->amount_paid, 2) }}</span></div>
            <div class="sale-detail-total"><span>Change</span><span>{{ $currencySymbol }}{{ number_format((float)$sale->change_amount, 2) }}</span></div>
        </div>

        <div style="margin-top:25px;padding-top:17px;border-top:1px dashed #cfdad3">
            <strong>Payment breakdown</strong>
            @forelse($sale->payments as $payment)
                <div class="sale-detail-total">
                    <span>{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }} · {{ ucfirst($payment->status) }}</span>
                    <strong>{{ $currencySymbol }}{{ number_format((float)$payment->amount, 2) }}</strong>
                </div>
                @if($payment->reference)
                    <div class="sale-detail-muted">Reference: {{ $payment->reference }}</div>
                @endif
            @empty
                <p class="sale-detail-muted">No payment records.</p>
            @endforelse
        </div>

        <p style="text-align:center;margin:28px 0 0;color:#718078">Thank you for your business.</p>
    </div>
</div>
@endsection
