@extends('layouts.app')

@section('title', 'Business Dashboard')
@section('page-heading', 'Dashboard')

@php
    /*
    |--------------------------------------------------------------------------
    | Retrieve the logged-in user's business from the database
    |--------------------------------------------------------------------------
    */
    $user = auth()->user();

    $businessName = $business->name;

    $currency = $business->currency ?? 'GHS';

    // Get the business currency from the database.
    $currency = $business?->currency ?? 'GHS';

    $currencySymbols = [
        'GHS' => '₵',
        'USD' => '$',
        'GBP' => '£',
        'EUR' => '€',
    ];

    $currencySymbol = $currencySymbols[$currency] ?? $currency;
@endphp


@push('styles')
<style>
    /* Force a clean, white background for the entire dashboard area */
    body {
        background-color: #f8fafc !important; 
    }

    .dash-container {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        color: #0f172a;
        max-width: 1400px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    /* --- TOP STATUS BAR --- */
    .dash-status-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 24px;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    .status-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .status-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .status-value {
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
    }
    
    .status-badge-text {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin-top: 4px;
        display: block;
        text-align: right;
    }

    /* --- STATS GRID --- */
    .dash-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 22px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .stat-title {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .stat-icon-wrapper {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }

    .stat-icon-wrapper svg {
        width: 18px;
        height: 18px;
    }

    .stat-main-value {
        font-size: 32px;
        font-weight: 900;
        color: #0f172a;
        line-height: 1;
        margin-bottom: 16px;
        letter-spacing: -0.5px;
    }

    .stat-footer {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
        font-weight: 600;
        color: #475569;
        margin-top: auto;
    }

    .stat-footer-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    /* Icon Colors */
    .icon-purple { background: #f3e8ff; color: #9333ea; }
    .icon-green  { background: #dcfce7; color: #16a34a; }
    .icon-blue   { background: #e0f2fe; color: #0284c7; }
    .icon-red    { background: #fee2e2; color: #dc2626; }

    /* --- BOTTOM PANELS --- */
    .dash-panels-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
    }

    .panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .panel-title {
        font-size: 18px;
        font-weight: 900;
        color: #0f172a;
        margin: 0 0 4px;
    }

    .panel-sub {
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        margin: 0;
    }

    /* Chart Placeholder */
    .chart-box {
        flex: 1;
        min-height: 280px;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 32px;
        background: #f8fafc;
    }

    .chart-box-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: #e2e8f0;
        color: #64748b;
        display: grid;
        place-items: center;
        margin-bottom: 16px;
    }

    .chart-box-title {
        font-size: 16px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 6px;
    }

    .chart-box-text {
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        max-width: 280px;
    }

    /* Inventory List */
    .inventory-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .inventory-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
    }

    .inventory-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .inventory-label {
        font-size: 14px;
        font-weight: 700;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .inventory-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .inventory-value {
        font-size: 16px;
        font-weight: 900;
        color: #0f172a;
    }

    /* Transactions Table */
    .tx-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }

    .tx-table th {
        text-align: left;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        padding-bottom: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .tx-empty {
        text-align: center;
        padding: 48px 0;
    }

    .tx-empty-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        border-radius: 12px;
        background: #f8fafc;
        color: #94a3b8;
        display: grid;
        place-items: center;
        border: 1px solid #e2e8f0;
    }

    .tx-empty-title {
        font-size: 14px;
        font-weight: 800;
        color: #1e293b;
        margin: 0 0 4px;
    }

    .tx-empty-text {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        margin: 0;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .dash-stats-grid { grid-template-columns: repeat(2, 1fr); }
        .dash-panels-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 640px) {
        .dash-stats-grid { grid-template-columns: 1fr; }
        .dash-status-bar { flex-direction: column; align-items: flex-start; }
        .status-badge-text { text-align: left; }
    }
</style>
@endpush

@section('content')
<div class="dash-container">

    {{-- ================= TOP STATUS BAR ================= --}}
    <div class="dash-status-bar">
        {{-- Business Info --}}
        <div class="status-item">
            <span class="status-label">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Business Workspace
            </span>
            <span class="status-value">{{ $businessName }}</span>
        </div>

        {{-- Currency / Date --}}
        <div class="status-item">
            <span class="status-label">System Settings</span>
            <span class="status-value">{{ $currency }} ({{ $currencySymbol }})</span>
        </div>

        {{-- System Status --}}
        <div class="status-item" style="align-items: flex-end;">
            <div class="status-badge">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                System Online
            </div>
            <span class="status-badge-text">{{ now()->format('d M Y') }}</span>
        </div>
    </div>

    {{-- ================= STATS GRID ================= --}}
    <div class="dash-stats-grid">
        
        {{-- Today's Sales --}}
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 9v1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Today's Sales
                </span>
                <div class="stat-icon-wrapper icon-purple">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 9v1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="stat-main-value">{{ $currencySymbol }}{{ number_format($todaySales, 2) }}</div>
            <div class="stat-footer">
                <span class="stat-footer-dot" style="background: #9333ea;"></span>
                Completed sales recorded today
            </div>
        </div>

        {{-- Monthly Revenue --}}
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Monthly Revenue
                </span>
                <div class="stat-icon-wrapper icon-green">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="stat-main-value">{{ $currencySymbol }}{{ number_format($monthlyRevenue, 2) }}</div>
            <div class="stat-footer">
                <span class="stat-footer-dot" style="background: #16a34a;"></span>
                Revenue for {{ now()->format('F Y') }}
            </div>
        </div>

        {{-- Total Products --}}
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Total Products
                </span>
                <div class="stat-icon-wrapper icon-blue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="stat-main-value">{{ $activeProducts }}</div>
            <div class="stat-footer">
                <span class="stat-footer-dot" style="background: #0284c7;"></span>
                Active products in your catalogue
            </div>
        </div>

        {{-- Low Stock --}}
        <div class="stat-card">
            <div class="stat-header">
                <span class="stat-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                    Low Stock
                </span>
                <div class="stat-icon-wrapper icon-red">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                </div>
            </div>
            <div class="stat-main-value">{{ $inventoryTotals["low_stock"] }}</div>
            <div class="stat-footer">
                <span class="stat-footer-dot" style="background: #dc2626;"></span>
                Products requiring replenishment
            </div>
        </div>
    </div>

    {{-- ================= BOTTOM PANELS ================= --}}
    <div class="dash-panels-grid">
        
        {{-- Left: Sales Overview --}}
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Sales Overview</h3>
                    <p class="panel-sub">Trends and performance over time</p>
                </div>
                <span class="status-badge" style="background: #f8fafc; border-color: #e2e8f0; color: #475569;">This Month</span>
            </div>

            <div style="width: 100%; min-height: 280px;">
                @php
                    $chartValues = $salesTrend->keyBy(fn ($item) => \Illuminate\Support\Carbon::parse($item->sale_date)->format('Y-m-d'));
                    $chartDays = collect(range(1, now()->daysInMonth))->map(function ($day) use ($chartValues) {
                        $date = now()->startOfMonth()->day($day);
                        $key = $date->format('Y-m-d');

                        return [
                            'day' => $day,
                            'label' => $date->format('d M'),
                            'total' => (float) ($chartValues->get($key)->total ?? 0),
                        ];
                    });
                    $chartMax = max(1, $chartDays->max('total'));
                @endphp

                @if($chartDays->sum('total') > 0)
                    <div style="display:flex; justify-content:space-between; align-items:end; gap:5px; height:240px; border-bottom:1px solid #e2e8f0; padding:10px 4px 0;">
                        @foreach($chartDays as $day)
                            <div title="{{ $day['label'] }}: {{ $currencySymbol }}{{ number_format($day['total'], 2) }}"
                                 style="flex:1; height:100%; display:flex; flex-direction:column; justify-content:end; align-items:center; min-width:0;">
                                <div style="width:100%; max-width:22px; height:{{ $day['total'] > 0 ? max(3, ($day['total'] / $chartMax) * 100) : 0 }}%; background:#16a34a; border-radius:5px 5px 0 0;"></div>
                            </div>
                        @endforeach
                    </div>
                    <div style="display:flex; justify-content:space-between; color:#64748b; font-size:11px; margin-top:10px;">
                        <span>1 {{ now()->format('M') }}</span>
                        <span>{{ now()->format('d M Y') }}</span>
                    </div>
                    <p class="panel-sub" style="margin-top:12px;">Daily completed sales · {{ $currencySymbol }}{{ number_format($monthlyRevenue, 2) }} this month</p>
                @else
                    <div class="chart-box">
                        <p class="chart-box-title">No completed sales this month</p>
                        <p class="chart-box-text">Daily sales performance will appear here when sales are recorded.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: Inventory Summary --}}
        <div class="panel">
            <div class="panel-header">
                <div>
                    <h3 class="panel-title">Inventory Summary</h3>
                    <p class="panel-sub">Current stock position</p>
                </div>
            </div>

            <div class="inventory-list">
                <div class="inventory-item">
                    <span class="inventory-label">
                        <span class="inventory-dot" style="background: #cbd5e1;"></span> Total Products
                    </span>
                    <span class="inventory-value">{{ $inventoryTotals["total"] }}</span>
                </div>
                <div class="inventory-item">
                    <span class="inventory-label">
                        <span class="inventory-dot" style="background: #16a34a;"></span> In Stock
                    </span>
                    <span class="inventory-value" style="color: #16a34a;">{{ $inventoryTotals["in_stock"] }}</span>
                </div>
                <div class="inventory-item">
                    <span class="inventory-label">
                        <span class="inventory-dot" style="background: #f59e0b;"></span> Low Stock
                    </span>
                    <span class="inventory-value" style="color: #f59e0b;">{{ $inventoryTotals["low_stock"] }}</span>
                </div>
                <div class="inventory-item">
                    <span class="inventory-label">
                        <span class="inventory-dot" style="background: #dc2626;"></span> Out of Stock
                    </span>
                    <span class="inventory-value" style="color: #dc2626;">{{ $inventoryTotals["out_of_stock"] }}</span>
                </div>
            </div>
            
            <p style="margin-top: 24px; font-size: 12px; font-weight: 600; color: #64748b;">
                Live stock position across your accessible branches.
            </p>
        </div>
    </div>

    {{-- ================= RECENT TRANSACTIONS ================= --}}
    <div class="panel" style="margin-top: 20px;">
        <div class="panel-header">
            <div>
                <h3 class="panel-title">Recent Transactions</h3>
                <p class="panel-sub">Latest activity across {{ $businessName }}</p>
            </div>
            <a href="{{ route('business.sales.index', ['business' => $business->slug]) }}"
               class="status-badge" style="text-decoration: none;">View All Sales</a>
        </div>

        <table class="tx-table">
            <thead>
                <tr>
                    <th>Transaction</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentSales as $sale)
                    <tr>
                        <td style="padding: 14px 0; font-weight: 700;">
                            <a href="{{ route('business.sales.show', ['business' => $business->slug, 'sale' => $sale->id]) }}">
                                {{ $sale->receipt_number }}
                            </a>
                        </td>
                        <td>{{ $sale->customer?->name ?? 'Walk-in Customer' }}</td>
                        <td>{{ $sale->sold_at?->format('d M Y, h:i A') ?? '—' }}</td>
                        <td style="font-weight: 800;">{{ $currencySymbol }}{{ number_format($sale->total_amount, 2) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $sale->payment_status)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="tx-empty">
                                <p class="tx-empty-title">No transactions recorded yet</p>
                                <p class="tx-empty-text">Completed sales will appear here.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection