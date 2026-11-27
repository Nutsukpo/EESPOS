@extends('layouts.app')

@section('title', 'Point of Sale')
@section('page-heading', 'Point of Sale')

@php
    $currency = $business->currency ?? 'GHS';

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
    :root {
        --pos-bg: #f5f7f5;
        --pos-surface: #ffffff;
        --pos-surface-2: #f8faf8;
        --pos-border: #e2e9e4;
        --pos-border-strong: #cfdad3;
        --pos-ink: #14261c;
        --pos-ink-2: #3a4d42;
        --pos-muted: #6f8077;
        --pos-muted-2: #93a29a;
        --pos-brand: #1a5c3f;
        --pos-brand-2: #237a54;
        --pos-brand-soft: #e8f3ec;
        --pos-accent: #d4a234;
        --pos-danger: #c0473c;
        --pos-danger-soft: #fbeceb;
        --pos-success: #1c8a52;
        --pos-success-soft: #e6f5ed;

        --pos-radius-sm: 8px;
        --pos-radius: 12px;
        --pos-radius-lg: 16px;
        --pos-radius-xl: 20px;

        --pos-shadow-sm: 0 1px 2px rgba(16, 43, 32, .05);
        --pos-shadow: 0 4px 16px rgba(16, 43, 32, .06);
        --pos-shadow-lg: 0 12px 32px rgba(16, 43, 32, .10);

        --pos-font: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    }

    .pos-page {
        max-width: 1800px;
        margin: 0 auto;
        padding: 16px;
        color: var(--pos-ink);
        font-family: var(--pos-font);
        background: var(--pos-bg);
        min-height: calc(100vh - 64px);
    }

    /* ===== Top bar ===== */
    .pos-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 18px;
        padding: 16px 20px;
        background: var(--pos-surface);
        border: 1px solid var(--pos-border);
        border-radius: var(--pos-radius-lg);
        box-shadow: var(--pos-shadow-sm);
    }

    .pos-heading {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .pos-heading-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--pos-brand) 0%, var(--pos-brand-2) 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        box-shadow: 0 4px 12px rgba(26, 92, 63, .25);
    }

    .pos-heading h2 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
        letter-spacing: -.01em;
        color: var(--pos-ink);
    }

    .pos-heading p {
        margin: 2px 0 0;
        color: var(--pos-muted);
        font-size: .8rem;
    }

    .pos-branch-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--pos-surface-2);
        border: 1px solid var(--pos-border);
        border-radius: var(--pos-radius);
        padding: 6px 8px 6px 14px;
    }

    .pos-branch-wrap label {
        font-size: .72rem;
        font-weight: 700;
        color: var(--pos-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
        margin: 0;
        white-space: nowrap;
    }

    .pos-branch-select {
        min-width: 200px;
        border: 1px solid transparent;
        border-radius: var(--pos-radius-sm);
        padding: 8px 12px;
        background: var(--pos-surface);
        color: var(--pos-ink);
        font-weight: 600;
        font-size: .85rem;
        outline: none;
        cursor: pointer;
        transition: border-color .15s, box-shadow .15s;
    }

    .pos-branch-select:focus {
        border-color: var(--pos-brand);
        box-shadow: 0 0 0 3px rgba(26, 92, 63, .12);
    }

    /* ===== Workspace ===== */
    .pos-workspace {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr) minmax(260px, .72fr);
        gap: 16px;
        align-items: start;
    }

    .pos-panel {
        background: var(--pos-surface);
        border: 1px solid var(--pos-border);
        border-radius: var(--pos-radius-lg);
        box-shadow: var(--pos-shadow-sm);
        min-width: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .pos-panel-head {
        padding: 15px 18px;
        border-bottom: 1px solid var(--pos-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        background: linear-gradient(180deg, #fff 0%, var(--pos-surface-2) 100%);
    }

    .pos-panel-head h3 {
        font-size: .95rem;
        font-weight: 800;
        margin: 0;
        color: var(--pos-ink);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pos-panel-head h3 i {
        color: var(--pos-brand);
        font-size: .9rem;
    }

    .pos-panel-head p {
        color: var(--pos-muted);
        font-size: .76rem;
        margin: 3px 0 0;
    }

    .pos-panel-badge {
        background: var(--pos-brand-soft);
        color: var(--pos-brand);
        font-size: .72rem;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 20px;
        white-space: nowrap;
    }

    /* ===== Search + categories ===== */
    .pos-search-wrap {
        padding: 14px 16px 8px;
        position: relative;
    }

    .pos-search-icon {
        position: absolute;
        left: 30px;
        top: 50%;
        transform: translateY(-40%);
        color: var(--pos-muted-2);
        font-size: .85rem;
        pointer-events: none;
    }

    .pos-search {
        width: 100%;
        border: 1px solid var(--pos-border);
        border-radius: var(--pos-radius);
        padding: 12px 14px 12px 38px;
        outline: none;
        font-size: .88rem;
        background: var(--pos-surface-2);
        color: var(--pos-ink);
        transition: border-color .15s, box-shadow .15s, background .15s;
    }

    .pos-search::placeholder { color: var(--pos-muted-2); }

    .pos-search:focus {
        border-color: var(--pos-brand);
        background: var(--pos-surface);
        box-shadow: 0 0 0 3px rgba(26, 92, 63, .1);
    }

    .pos-category-list {
        display: flex;
        gap: 8px;
        padding: 8px 16px 14px;
        overflow-x: auto;
        scrollbar-width: thin;
    }

    .pos-category-list::-webkit-scrollbar { height: 6px; }
    .pos-category-list::-webkit-scrollbar-thumb {
        background: var(--pos-border-strong);
        border-radius: 3px;
    }

    .pos-category {
        border: 1px solid var(--pos-border);
        background: var(--pos-surface);
        color: var(--pos-ink-2);
        border-radius: 30px;
        padding: 7px 14px;
        white-space: nowrap;
        font-size: .76rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s;
    }

    .pos-category:hover {
        border-color: var(--pos-brand-2);
        color: var(--pos-brand);
    }

    .pos-category.active {
        background: var(--pos-brand);
        border-color: var(--pos-brand);
        color: #fff;
        box-shadow: 0 2px 8px rgba(26, 92, 63, .25);
    }

    /* ===== Product grid ===== */
    .pos-product-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        padding: 0 16px 16px;
        overflow-y: auto;
        max-height: calc(100vh - 340px);
    }

    .pos-product-card {
        border: 1px solid var(--pos-border);
        border-radius: var(--pos-radius);
        padding: 10px;
        background: var(--pos-surface);
        text-align: left;
        cursor: pointer;
        transition: transform .15s, box-shadow .15s, border-color .15s;
        min-width: 0;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .pos-product-card:hover:not(:disabled) {
        border-color: var(--pos-brand-2);
        transform: translateY(-2px);
        box-shadow: var(--pos-shadow);
    }

    .pos-product-card:active:not(:disabled) {
        transform: translateY(0);
    }

    .pos-product-card:disabled {
        opacity: .55;
        cursor: not-allowed;
        background: var(--pos-surface-2);
    }

    .pos-product-icon {
        height: 90px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #eef5f0 0%, #e3efe7 100%);
        border-radius: var(--pos-radius-sm);
        color: var(--pos-brand);
        font-size: 1.7rem;
        margin-bottom: 9px;
        overflow: hidden;
        position: relative;
    }

    .pos-product-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pos-product-stock-badge {
        position: absolute;
        top: 6px;
        right: 6px;
        background: rgba(255, 255, 255, .95);
        backdrop-filter: blur(4px);
        color: var(--pos-ink-2);
        font-size: .64rem;
        font-weight: 800;
        padding: 3px 7px;
        border-radius: 20px;
        border: 1px solid var(--pos-border);
    }

    .pos-product-stock-badge.low {
        background: #fff4e5;
        color: #a86b12;
        border-color: #f3d9b0;
    }

    .pos-product-stock-badge.out {
        background: var(--pos-danger-soft);
        color: var(--pos-danger);
        border-color: #f0cdc9;
    }

    .pos-product-name {
        font-size: .8rem;
        font-weight: 800;
        line-height: 1.35;
        min-height: 32px;
        color: var(--pos-ink);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .pos-product-variation {
        font-size: .7rem;
        color: var(--pos-muted);
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pos-product-price {
        color: var(--pos-brand);
        font-size: .92rem;
        font-weight: 900;
        margin-top: auto;
        padding-top: 8px;
        letter-spacing: -.01em;
    }

    .pos-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 50px 15px;
        color: var(--pos-muted);
    }

    .pos-empty i {
        display: block;
        font-size: 2.2rem;
        color: var(--pos-border-strong);
        margin-bottom: 12px;
    }

    .pos-empty-title {
        font-weight: 700;
        color: var(--pos-ink-2);
        margin-bottom: 4px;
    }

    .pos-empty-sub {
        font-size: .8rem;
        color: var(--pos-muted);
    }

    /* ===== Skeleton loader ===== */
    .pos-skeleton {
        border: 1px solid var(--pos-border);
        border-radius: var(--pos-radius);
        padding: 10px;
        background: var(--pos-surface);
    }

    .pos-skeleton-block {
        background: linear-gradient(90deg, #eef2ef 25%, #f6f8f6 50%, #eef2ef 75%);
        background-size: 200% 100%;
        animation: pos-shimmer 1.4s infinite;
        border-radius: 6px;
    }

    @keyframes pos-shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* ===== Cart column ===== */
    .pos-right-column {
        display: contents;
    }

    .pos-customer {
        padding: 14px 18px;
        border-bottom: 1px solid var(--pos-border);
        background: var(--pos-surface-2);
    }

    .pos-customer label {
        display: block;
        font-size: .72rem;
        font-weight: 800;
        color: var(--pos-muted);
        margin-bottom: 7px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .pos-customer select {
        width: 100%;
        border: 1px solid var(--pos-border);
        border-radius: var(--pos-radius-sm);
        padding: 10px 12px;
        background: var(--pos-surface);
        color: var(--pos-ink);
        font-size: .85rem;
        font-weight: 600;
        outline: none;
        cursor: pointer;
    }

    .pos-customer select:focus {
        border-color: var(--pos-brand);
        box-shadow: 0 0 0 3px rgba(26, 92, 63, .1);
    }

    .pos-cart-items {
        padding: 6px 18px;
        max-height: 360px;
        overflow-y: auto;
        flex: 1;
    }

    .pos-cart-items::-webkit-scrollbar { width: 6px; }
    .pos-cart-items::-webkit-scrollbar-thumb {
        background: var(--pos-border-strong);
        border-radius: 3px;
    }

    .pos-cart-empty {
        padding: 44px 12px;
        text-align: center;
        color: var(--pos-muted);
    }

    .pos-cart-empty i {
        display: block;
        font-size: 2.2rem;
        margin-bottom: 12px;
        color: var(--pos-border-strong);
    }

    .pos-cart-empty-title {
        font-weight: 700;
        color: var(--pos-ink-2);
        margin-bottom: 4px;
    }

    .pos-cart-empty-sub {
        font-size: .78rem;
    }

    .pos-cart-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        padding: 12px 0;
        border-bottom: 1px solid var(--pos-border);
    }

    .pos-cart-item:last-child { border-bottom: 0; }

    .pos-cart-name {
        font-size: .82rem;
        font-weight: 800;
        color: var(--pos-ink);
        line-height: 1.3;
    }

    .pos-cart-unit {
        font-size: .72rem;
        color: var(--pos-muted);
        margin-top: 2px;
    }

    .pos-cart-controls {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 9px;
    }

    .pos-qty-btn {
        width: 28px;
        height: 28px;
        border: 1px solid var(--pos-border-strong);
        border-radius: var(--pos-radius-sm);
        background: var(--pos-surface);
        color: var(--pos-ink);
        font-weight: 800;
        font-size: .9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .15s, border-color .15s;
    }

    .pos-qty-btn:hover {
        background: var(--pos-brand-soft);
        border-color: var(--pos-brand-2);
        color: var(--pos-brand);
    }

    .pos-qty-value {
        min-width: 26px;
        text-align: center;
        font-size: .82rem;
        font-weight: 800;
        color: var(--pos-ink);
    }

    .pos-remove {
        border: 0;
        background: transparent;
        color: var(--pos-danger);
        font-size: .72rem;
        font-weight: 700;
        padding: 4px 6px;
        margin-left: auto;
        cursor: pointer;
        border-radius: 6px;
        transition: background .15s;
    }

    .pos-remove:hover { background: var(--pos-danger-soft); }

    .pos-cart-line-total {
        text-align: right;
        font-size: .85rem;
        font-weight: 900;
        white-space: nowrap;
        color: var(--pos-ink);
    }

    /* ===== Summary ===== */
    .pos-summary {
        padding: 14px 18px;
        border-top: 1px solid var(--pos-border);
        background: var(--pos-surface-2);
    }

    .pos-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin: 8px 0;
        font-size: .82rem;
        color: var(--pos-ink-2);
    }

    .pos-summary-row span:first-child {
        color: var(--pos-muted);
        font-weight: 600;
    }

    .pos-summary-row strong {
        color: var(--pos-ink);
        font-weight: 800;
    }

    .pos-summary-total {
        border-top: 1px dashed var(--pos-border-strong);
        padding-top: 14px;
        margin-top: 12px;
        font-size: 1.1rem;
    }

    .pos-summary-total span:first-child {
        color: var(--pos-ink);
        font-weight: 800;
    }

    .pos-summary-total strong {
        color: var(--pos-brand);
        font-size: 1.2rem;
        font-weight: 900;
    }

    .pos-discount {
        width: 100px;
        border: 1px solid var(--pos-border);
        border-radius: var(--pos-radius-sm);
        padding: 6px 9px;
        text-align: right;
        font-size: .82rem;
        font-weight: 700;
        background: var(--pos-surface);
        color: var(--pos-ink);
        outline: none;
    }

    .pos-discount:focus {
        border-color: var(--pos-brand);
        box-shadow: 0 0 0 3px rgba(26, 92, 63, .1);
    }

    /* ===== Checkout ===== */
    .pos-checkout {
        padding: 14px 18px 18px;
        border-top: 1px solid var(--pos-border);
    }

    .pos-section-label {
        font-size: .72rem;
        font-weight: 800;
        color: var(--pos-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 9px;
    }

    .pos-payment-methods {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 14px;
    }

    .pos-payment-option {
        border: 1px solid var(--pos-border);
        border-radius: var(--pos-radius);
        padding: 10px 6px;
        background: var(--pos-surface);
        color: var(--pos-ink-2);
        font-size: .72rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        min-height: 62px;
        justify-content: center;
        transition: all .15s;
    }

    .pos-payment-option i {
        font-size: 1rem;
        color: var(--pos-muted);
        transition: color .15s;
    }

    .pos-payment-option:hover {
        border-color: var(--pos-brand-2);
    }

    .pos-payment-option.active {
        border-color: var(--pos-brand);
        background: var(--pos-brand-soft);
        color: var(--pos-brand);
        box-shadow: 0 0 0 1px var(--pos-brand) inset;
    }

    .pos-payment-option.active i { color: var(--pos-brand); }

    .pos-cash-input-wrap {
        margin-bottom: 12px;
    }

    .pos-cash-input-wrap label {
        display: block;
        font-size: .72rem;
        font-weight: 800;
        color: var(--pos-muted);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .pos-cash-input {
        width: 100%;
        border: 1px solid var(--pos-border);
        border-radius: var(--pos-radius-sm);
        padding: 11px 13px;
        font-size: 1rem;
        font-weight: 800;
        color: var(--pos-ink);
        background: var(--pos-surface);
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }

    .pos-cash-input:focus {
        border-color: var(--pos-brand);
        box-shadow: 0 0 0 3px rgba(26, 92, 63, .1);
    }

    .pos-change-preview {
        margin-top: 7px;
        font-size: .78rem;
        color: var(--pos-muted);
        display: flex;
        justify-content: space-between;
    }

    .pos-change-preview strong {
        color: var(--pos-success);
        font-weight: 800;
    }

    .pos-checkout-btn {
        width: 100%;
        border: 0;
        border-radius: var(--pos-radius);
        background: linear-gradient(135deg, var(--pos-brand) 0%, var(--pos-brand-2) 100%);
        color: #fff;
        padding: 14px;
        font-weight: 800;
        font-size: .9rem;
        cursor: pointer;
        transition: transform .1s, box-shadow .15s, opacity .15s;
        box-shadow: 0 4px 14px rgba(26, 92, 63, .28);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .pos-checkout-btn:hover:not(:disabled) {
        box-shadow: 0 6px 20px rgba(26, 92, 63, .35);
    }

    .pos-checkout-btn:active:not(:disabled) {
        transform: scale(.98);
    }

    .pos-checkout-btn:disabled {
        background: var(--pos-border-strong);
        box-shadow: none;
        cursor: not-allowed;
        opacity: .7;
    }

    .pos-checkout-note {
        text-align: center;
        color: var(--pos-muted);
        font-size: .72rem;
        margin-top: 10px;
        line-height: 1.5;
    }

    /* ===== Receipt ===== */
    .pos-receipt {
        padding: 16px;
        font-family: 'SF Mono', 'Menlo', 'Consolas', monospace;
        font-size: .76rem;
        color: var(--pos-ink-2);
        background: #fff;
        position: relative;
    }

    .pos-receipt::before,
    .pos-receipt::after {
        content: '';
        position: absolute;
        left: 0; right: 0;
        height: 8px;
        background-image: radial-gradient(circle, var(--pos-surface) 3px, transparent 3px);
        background-size: 12px 12px;
        background-position: 0 0;
    }

    .pos-receipt::before { top: -4px; }
    .pos-receipt::after { bottom: -4px; background-position: 6px 0; }

    .pos-receipt-brand {
        text-align: center;
        padding-bottom: 12px;
        border-bottom: 1px dashed var(--pos-border-strong);
    }

    .pos-receipt-brand strong {
        display: block;
        font-size: 1rem;
        color: var(--pos-ink);
        font-family: var(--pos-font);
        font-weight: 900;
        letter-spacing: -.01em;
    }

    .pos-receipt-brand span {
        display: block;
        color: var(--pos-muted);
        font-size: .72rem;
        margin-top: 3px;
        font-family: var(--pos-font);
    }

    .pos-receipt-brand .pos-receipt-type {
        text-transform: uppercase;
        letter-spacing: .12em;
        font-weight: 800;
        color: var(--pos-brand);
        margin-top: 6px;
    }

    .pos-receipt-details {
        padding: 10px 0;
        font-size: .72rem;
        color: var(--pos-muted);
        line-height: 1.9;
    }

    .pos-receipt-details .row {
        display: flex;
        justify-content: space-between;
        gap: 8px;
    }

    .pos-receipt-details .row span:last-child {
        color: var(--pos-ink-2);
        font-weight: 600;
        text-align: right;
    }

    .pos-receipt-items {
        border-top: 1px dashed var(--pos-border-strong);
        border-bottom: 1px dashed var(--pos-border-strong);
        padding: 10px 0;
        font-size: .72rem;
    }

    .pos-receipt-item {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        margin: 6px 0;
    }

    .pos-receipt-item span:first-child {
        color: var(--pos-ink-2);
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .pos-receipt-item strong {
        color: var(--pos-ink);
        white-space: nowrap;
    }

    .pos-receipt-totals .row {
        display: flex;
        justify-content: space-between;
        padding: 3px 0;
    }

    .pos-receipt-totals .row.grand {
        border-top: 1px dashed var(--pos-border-strong);
        margin-top: 6px;
        padding-top: 8px;
        font-size: .9rem;
        font-weight: 900;
        color: var(--pos-ink);
        font-family: var(--pos-font);
    }

    .pos-receipt-totals .row.grand span:last-child {
        color: var(--pos-brand);
    }

    .pos-receipt-footer {
        text-align: center;
        color: var(--pos-muted);
        font-size: .7rem;
        padding-top: 14px;
        line-height: 1.6;
    }

    .pos-receipt-footer .thanks {
        font-family: var(--pos-font);
        font-weight: 800;
        color: var(--pos-ink-2);
        margin-bottom: 3px;
    }

    /* ===== Classic payment receipt layout ===== */
    .pos-receipt-classic {
        color: #000 !important;
        background: #fff !important;
        font-family: 'Courier New', 'SF Mono', monospace !important;
        font-size: 11px !important;
        line-height: 1.45;
        padding: 12px !important;
    }
    .pos-receipt-classic .pos-receipt-brand {
        text-align: center;
        border-bottom: 2px solid #000;
        padding: 0 0 9px;
        margin-bottom: 7px;
    }
    .pos-receipt-classic .pos-receipt-brand strong {
        color: #000 !important;
        font-family: inherit !important;
        font-size: 15px !important;
        font-weight: 900 !important;
        letter-spacing: .02em;
    }
    .pos-receipt-classic .pos-receipt-brand span {
        color: #000 !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        margin-top: 2px;
    }
    .pos-receipt-classic .pos-receipt-brand .pos-receipt-type {
        font-size: 12px !important;
        font-weight: 900 !important;
        letter-spacing: .04em;
        margin-top: 5px;
    }
    .pos-receipt-classic .pos-receipt-details {
        color: #000 !important;
        font-size: 10px !important;
        line-height: 1.65;
        padding: 4px 0 8px;
        border-bottom: 2px solid #000;
    }
    .pos-receipt-classic .pos-receipt-details .receipt-meta-row {
        display: grid !important;
        grid-template-columns: 72px minmax(0, 1fr);
        align-items: baseline;
        column-gap: 8px;
        width: 100%;
        margin: 0 !important;
        padding: 2px 0;
        line-height: 1.4;
    }
    .pos-receipt-classic .pos-receipt-details .receipt-meta-row span {
        display: block;
        min-width: 0;
        color: #000 !important;
        font-weight: 700 !important;
    }
    .pos-receipt-classic .pos-receipt-details .receipt-meta-row span:first-child {
        white-space: nowrap;
        text-align: left;
    }
    .pos-receipt-classic .pos-receipt-details .receipt-meta-row span:last-child {
        text-align: right;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .pos-receipt-table { margin-top: 7px; }
    .pos-receipt-table-head,
    .pos-receipt-classic .pos-receipt-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 28px 52px 58px;
        gap: 4px;
        align-items: start;
    }
    .pos-receipt-table-head {
        color: #000;
        font-weight: 900;
        font-size: 9px;
        border-bottom: 1px dashed #000;
        padding: 2px 0 5px;
    }
    .pos-receipt-table-head span:not(:first-child),
    .pos-receipt-classic .item-qty,
    .pos-receipt-classic .item-price,
    .pos-receipt-classic .item-total { text-align: right; }
    .pos-receipt-classic .pos-receipt-items {
        border: 0;
        padding: 5px 0 7px;
        font-size: 10px !important;
    }
    .pos-receipt-classic .pos-receipt-item {
        color: #000 !important;
        margin: 4px 0;
        gap: 4px;
    }
    .pos-receipt-classic .pos-receipt-item span,
    .pos-receipt-classic .pos-receipt-item strong {
        color: #000 !important;
        font-weight: 700 !important;
        white-space: normal;
        overflow-wrap: anywhere;
    }
    .pos-receipt-classic .pos-receipt-item .item-name {
        min-width: 0;
    }
    .pos-receipt-classic .pos-receipt-item .item-qty,
    .pos-receipt-classic .pos-receipt-item .item-price,
    .pos-receipt-classic .pos-receipt-item .item-total {
        white-space: nowrap;
    }
    .pos-receipt-empty {
        text-align: center;
        padding: 9px 0;
        color: #000;
        font-weight: 700;
    }
    .pos-receipt-classic .pos-receipt-totals {
        border-top: 1px dashed #000;
        padding-top: 6px;
        color: #000 !important;
        font-size: 10px !important;
    }
    .pos-receipt-classic .pos-receipt-totals .row {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        padding: 2px 0;
        color: #000 !important;
        font-weight: 700 !important;
    }
    .pos-receipt-classic .pos-receipt-totals .row span {
        color: #000 !important;
        font-weight: inherit !important;
    }
    .pos-receipt-classic .pos-receipt-totals .row.grand {
        border-top: 1px solid #000;
        margin-top: 4px;
        padding-top: 6px;
        font-size: 11px !important;
        font-weight: 900 !important;
        font-family: inherit !important;
    }
    .pos-receipt-classic .pos-receipt-totals .row.paid {
        font-weight: 900 !important;
    }
    .pos-receipt-status {
        text-align: center;
        border-top: 3px double #000;
        border-bottom: 3px double #000;
        margin-top: 8px;
        padding: 7px 2px;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        color: #000;
    }
    .pos-receipt-classic .pos-receipt-footer {
        color: #000 !important;
        font-size: 9px !important;
        font-weight: 700 !important;
        padding-top: 9px;
        line-height: 1.5;
    }
    .pos-receipt-classic .pos-receipt-footer .thanks {
        color: #000 !important;
        font-weight: 900 !important;
        font-size: 10px;
    }

    /* ===== Toast ===== */
    .pos-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: var(--pos-ink);
        color: #fff;
        padding: 13px 18px;
        border-radius: var(--pos-radius);
        box-shadow: var(--pos-shadow-lg);
        font-size: .85rem;
        font-weight: 600;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 10px;
        opacity: 0;
        transform: translateY(12px);
        transition: opacity .25s, transform .25s;
        max-width: 380px;
    }

    .pos-toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    .pos-toast.success { background: var(--pos-success); }
    .pos-toast.error { background: var(--pos-danger); }

    .pos-toast i { font-size: 1rem; }

    /* ===== Receipt printing ===== */
    @media print {
        @page {
            size: 80mm auto;
            margin: 4mm;
        }

        html, body {
            width: 80mm !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        body * {
            visibility: hidden !important;
        }

        .pos-receipt,
        .pos-receipt * {
            visibility: visible !important;
        }

        .pos-receipt {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 72mm !important;
            max-width: 72mm !important;
            padding: 2mm !important;
            margin: 0 !important;
            box-shadow: none !important;
            border: 0 !important;
            color: #000 !important;
        }

        .pos-receipt::before,
        .pos-receipt::after {
            display: none !important;
        }
        .pos-receipt-classic,
        .pos-receipt-classic * {
            color: #000 !important;
            font-family: 'Courier New', 'SF Mono', monospace !important;
            font-weight: 700 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .pos-receipt-classic .pos-receipt-brand strong,
        .pos-receipt-classic .pos-receipt-type,
        .pos-receipt-classic .pos-receipt-status,
        .pos-receipt-classic .grand,
        .pos-receipt-classic .paid {
            font-weight: 900 !important;
        }

        /* Make every printed receipt text element dark and easy to read. */
        .pos-receipt,
        .pos-receipt * {
            color: #000 !important;
            font-weight: 700 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Keep headings, item amounts and the grand total extra prominent. */
        .pos-receipt strong,
        .pos-receipt b,
        .pos-receipt .pos-receipt-type,
        .pos-receipt .pos-receipt-totals .row.grand,
        .pos-receipt .pos-receipt-footer .thanks {
            font-weight: 900 !important;
        }
    }

    /* ===== Responsive ===== */
    @media (max-width: 1200px) {
        .pos-workspace {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        }
        .pos-workspace > .pos-panel:last-child {
            grid-column: 1 / -1;
            max-height: 420px;
            overflow-y: auto;
        }
        .pos-product-grid {
            max-height: calc(100vh - 380px);
        }
    }

    @media (max-width: 900px) {
        .pos-workspace {
            grid-template-columns: minmax(0, 1fr);
        }
        .pos-workspace > .pos-panel:last-child {
            grid-column: auto;
            max-height: none;
        }
        .pos-product-grid {
            max-height: none;
        }
        .pos-payment-methods {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 650px) {
        .pos-page { padding: 10px; }
        .pos-topbar {
            flex-direction: column;
            align-items: stretch;
            padding: 14px;
        }
        .pos-branch-wrap { width: 100%; }
        .pos-branch-select { flex: 1; min-width: 0; }
        .pos-product-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            padding: 0 12px 12px;
        }
        .pos-search-wrap,
        .pos-category-list { padding-left: 12px; padding-right: 12px; }
        .pos-panel-head { padding: 12px 14px; }
        .pos-customer,
        .pos-cart-items,
        .pos-summary,
        .pos-checkout { padding-left: 14px; padding-right: 14px; }
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: .01ms !important;
            transition-duration: .01ms !important;
        }
    }
</style>
@endpush

@section('content')
<div class="pos-page">

    <div class="pos-topbar">
        <div class="pos-heading">
            <div class="pos-heading-icon">
                <i class="fas fa-cash-register"></i>
            </div>
            <div>
                <h2>Point of Sale</h2>
                <p>{{ $business->name }} · Fast and secure checkout</p>
            </div>
        </div>

        <div class="pos-branch-wrap">
            <label for="posBranch">Selling branch</label>
            <select id="posBranch" class="pos-branch-select">
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}"
                        {{ $selectedBranch->id === $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}{{ $branch->is_main ? ' (Main)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="pos-workspace">

        <!-- Products -->
        <section class="pos-panel">
            <div class="pos-panel-head">
                <div>
                    <h3><i class="fas fa-boxes-stacked"></i> Products</h3>
                    <p>Search by name, SKU, or scan a barcode</p>
                </div>
            </div>

            <div class="pos-search-wrap">
                <i class="fas fa-magnifying-glass pos-search-icon"></i>
                <input
                    type="search"
                    id="posSearch"
                    class="pos-search"
                    placeholder="Search products or scan barcode..."
                    autocomplete="off"
                    aria-label="Search products"
                >
            </div>

            <div class="pos-category-list" id="posCategories" role="tablist">
                <button type="button" class="pos-category active" data-category="all">
                    All products
                </button>
            </div>

            <div class="pos-product-grid" id="posProductGrid">
                <div class="pos-empty">
                    <i class="fas fa-magnifying-glass"></i>
                    <div class="pos-empty-title">Start typing to search</div>
                    <div class="pos-empty-sub">Find products by name, SKU, or barcode</div>
                </div>
            </div>
        </section>

        <!-- Cart + checkout -->
        <section class="pos-panel">
            <div class="pos-panel-head">
                <div>
                    <h3><i class="fas fa-shopping-cart"></i> Current Sale</h3>
                    <p id="posCartCount">0 items</p>
                </div>
            </div>

            <div class="pos-customer">
                <label for="posCustomer">Customer</label>
                <select id="posCustomer">
                    <option value="">Walk-in Customer</option>
                </select>
            </div>

            <div class="pos-cart-items" id="posCartItems">
                <div class="pos-cart-empty">
                    <i class="fas fa-basket-shopping"></i>
                    <div class="pos-cart-empty-title">Your cart is empty</div>
                    <div class="pos-cart-empty-sub">Add products from the left to begin</div>
                </div>
            </div>

            <div class="pos-summary">
                <div class="pos-summary-row">
                    <span>Subtotal</span>
                    <strong id="posSubtotal">{{ $currencySymbol }}0.00</strong>
                </div>

                <div class="pos-summary-row">
                    <span>Discount</span>
                    <input type="number" id="posDiscount"
                        class="pos-discount" value="0" min="0" step="0.01"
                        aria-label="Discount amount">
                </div>

                <div class="pos-summary-row">
                    <span>Tax</span>
                    <strong id="posTax">{{ $currencySymbol }}0.00</strong>
                </div>

                <div class="pos-summary-row pos-summary-total">
                    <span>Total</span>
                    <strong id="posTotal">{{ $currencySymbol }}0.00</strong>
                </div>
            </div>

            <div class="pos-checkout">
                <div class="pos-section-label">Payment method</div>

                <div class="pos-payment-methods" id="posPaymentMethods" role="radiogroup">
                    <button type="button" class="pos-payment-option active" data-method="cash" role="radio" aria-checked="true">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Cash</span>
                    </button>
                    <button type="button" class="pos-payment-option" data-method="mobile_money" role="radio" aria-checked="false">
                        <i class="fas fa-mobile-screen"></i>
                        <span>Mobile Money</span>
                    </button>
                    <button type="button" class="pos-payment-option" data-method="card" role="radio" aria-checked="false">
                        <i class="fas fa-credit-card"></i>
                        <span>Card</span>
                    </button>
                </div>

                <div class="pos-cash-input-wrap">
                    <label for="posAmountReceived">Cash amount received</label>
                    <input type="number" id="posAmountReceived"
                        class="pos-cash-input" min="0" step="0.01"
                        placeholder="0.00" inputmode="decimal">
                    <div class="pos-change-preview">
                        <span>Change</span>
                        <strong id="posChangePreview">{{ $currencySymbol }}0.00</strong>
                    </div>
                </div>

                <button type="button" class="pos-checkout-btn" id="posCheckoutButton" disabled>
                    <i class="fas fa-check-circle"></i>
                    <span>Complete cash sale</span>
                </button>
                <div class="pos-checkout-note" id="posCheckoutNote">
                    Cash checkout is available. Other payment methods need their payment confirmation setup.
                </div>
            </div>
        </section>

        <!-- Receipt -->
        <section class="pos-panel">
            <div class="pos-panel-head">
                <div>
                    <h3><i class="fas fa-receipt"></i> Receipt Preview</h3>
                    <p>Live preview of the current sale</p>
                </div>
                <button type="button" class="pos-category" id="posPrintReceipt" disabled>
                    <i class="fas fa-print"></i> Print receipt
                </button>
            </div>

            <div class="pos-receipt pos-receipt-classic">
                <div class="pos-receipt-brand">
                    <strong>{{ $business->name }}</strong>
                    @if($business->address)
                        <span>{{ $business->address }}</span>
                    @endif
                    @if($business->phone)
                        <span>Tel: {{ $business->phone }}</span>
                    @endif
                    <span>{{ $selectedBranch->name }}</span>
                    <span class="pos-receipt-type">Payment Receipt</span>
                </div>

                <div class="pos-receipt-details">
                    <div class="receipt-meta-row"><span>Receipt #</span><span id="posReceiptNumber">Pending checkout</span></div>
                    <div class="receipt-meta-row"><span>Date</span><span id="posReceiptDate"></span></div>
                    <div class="receipt-meta-row"><span>Method</span><span id="posReceiptMethod">Cash</span></div>
                    <div class="receipt-meta-row"><span>Cashier</span><span id="posReceiptCashier">{{ auth()->user()->name }}</span></div>
                </div>

                <div class="pos-receipt-table">
                    <div class="pos-receipt-table-head">
                        <span>ITEM</span><span>QTY</span><span>PRICE</span><span>TOTAL</span>
                    </div>
                    <div class="pos-receipt-items" id="posReceiptItems">
                        <div class="pos-receipt-empty">No items added</div>
                    </div>
                </div>

                <div class="pos-receipt-totals" id="posReceiptTotals">
                    <div class="row"><span>SUBTOTAL</span><span id="posReceiptSubtotal">{{ $currencySymbol }}0.00</span></div>
                    <div class="row grand"><span>ORDER TOTAL</span><span id="posReceiptTotal">{{ $currencySymbol }}0.00</span></div>
                    <div class="row paid"><span>AMOUNT PAID</span><span id="posReceiptAmountPaid">{{ $currencySymbol }}0.00</span></div>
                    <div class="row"><span>CHANGE</span><span id="posReceiptChange">{{ $currencySymbol }}0.00</span></div>
                </div>

                <div class="pos-receipt-status" id="posReceiptStatus">PENDING CHECKOUT</div>

                <div class="pos-receipt-footer">
                    <div class="thanks">Thank you for your business!</div>
                    <div>Please retain this receipt for your records.</div>
                </div>
            </div>
        </section>

    </div>
</div>

<div class="pos-toast" id="posToast">
    <i class="fas fa-circle-check"></i>
    <span id="posToastMessage"></span>
</div>

<style>
    #posLockOverlay { display:none; position:fixed; inset:0; z-index:100000; align-items:center; justify-content:center; padding:24px; background:rgba(9,25,17,.78); backdrop-filter:blur(7px); }
    #posLockOverlay.is-visible { display:flex; }
    .pos-lock-card { width:min(100%,430px); padding:32px; border-radius:18px; background:#fff; color:#14261c; box-shadow:0 24px 80px rgba(0,0,0,.28); }
    .pos-lock-icon { display:grid; place-items:center; width:54px; height:54px; margin-bottom:18px; border-radius:15px; background:#e8f3ec; color:#1a5c3f; font-size:24px; }
    .pos-lock-card h2 { margin:0 0 8px; font-size:24px; font-weight:750; }
    .pos-lock-card p { margin:0 0 22px; color:#64756b; line-height:1.55; }
    .pos-lock-card label { display:block; margin:14px 0 6px; font-size:13px; font-weight:650; }
    .pos-lock-card input { box-sizing:border-box; width:100%; min-height:46px; padding:10px 12px; border:1px solid #cfdad3; border-radius:9px; background:#fff; color:#14261c; font:inherit; }
    .pos-lock-card input:focus { outline:3px solid rgba(26,92,63,.14); border-color:#1a5c3f; }
    .pos-lock-submit { width:100%; min-height:47px; margin-top:20px; border:0; border-radius:9px; background:#1a5c3f; color:#fff; font:inherit; font-weight:700; cursor:pointer; }
    .pos-lock-submit:disabled { opacity:.65; cursor:wait; }
    #posLockError { min-height:20px; margin-top:10px; color:#b42318; font-size:13px; }
    @media (max-width:480px) { .pos-lock-card { padding:24px; } }
</style>

<div id="posLockOverlay" role="dialog" aria-modal="true" aria-labelledby="posLockTitle"
     aria-hidden="{{ $posLocked ? 'false' : 'true' }}" class="{{ $posLocked ? 'is-visible' : '' }}">
    <div class="pos-lock-card">
        <div class="pos-lock-icon"><i class="fas fa-lock"></i></div>
        <h2 id="posLockTitle">POS locked</h2>
        <p>This till has been locked after inactivity. Your unfinished sale is still here. Sign in again to continue.</p>
        <form id="posUnlockForm">
            <label for="posUnlockEmail">Email address</label>
            <input id="posUnlockEmail" name="email" type="email" autocomplete="username" required maxlength="254" placeholder="Enter your email address">
            <label for="posUnlockPassword">Password</label>
            <input id="posUnlockPassword" name="password" type="password" autocomplete="current-password" required>
            <div id="posLockError" role="alert" aria-live="polite"></div>
            <button class="pos-lock-submit" id="posUnlockButton" type="submit">Unlock POS</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const currency = @json($currencySymbol);
    const searchUrl = @json(route('business.pos.products.search', ['business' => $business->slug]));
    const checkoutUrl = @json(route('business.pos.checkout', ['business' => $business->slug]));
    const lockUrl = @json(route('business.pos.lock', ['business' => $business->slug]));
    const activityUrl = @json(route('business.pos.activity', ['business' => $business->slug]));
    const unlockUrl = @json(route('business.pos.unlock', ['business' => $business->slug]));
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const searchInput = document.getElementById('posSearch');
    const branchSelect = document.getElementById('posBranch');
    const productGrid = document.getElementById('posProductGrid');
    const categoryContainer = document.getElementById('posCategories');
    const cartContainer = document.getElementById('posCartItems');
    const discountInput = document.getElementById('posDiscount');
    const amountReceivedInput = document.getElementById('posAmountReceived');
    const checkoutButton = document.getElementById('posCheckoutButton');
    const checkoutNote = document.getElementById('posCheckoutNote');
    const changePreview = document.getElementById('posChangePreview');
    const customerSelect = document.getElementById('posCustomer');
    const toast = document.getElementById('posToast');
    const toastMessage = document.getElementById('posToastMessage');
    const printButton = document.getElementById('posPrintReceipt');

    let products = [];
    let cart = [];
    let activeCategory = 'all';
    let paymentMethod = 'cash';
    let toastTimer;

    const money = value => currency + Number(value || 0).toFixed(2);

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        })[char]);
    }

    function showToast(message, type = 'success') {
        clearTimeout(toastTimer);
        toastMessage.textContent = message;
        toast.className = 'pos-toast show ' + type;
        toast.querySelector('i').className = type === 'error'
            ? 'fas fa-circle-exclamation'
            : 'fas fa-circle-check';
        toastTimer = setTimeout(() => toast.classList.remove('show'), 4000);
    }

    const posLockOverlay = document.getElementById('posLockOverlay');
    const posUnlockForm = document.getElementById('posUnlockForm');
    const posUnlockEmail = document.getElementById('posUnlockEmail');
    const posUnlockPassword = document.getElementById('posUnlockPassword');
    const posUnlockButton = document.getElementById('posUnlockButton');
    const posLockError = document.getElementById('posLockError');
    const serverInitiallyLocked = @json($posLocked);
    const posIdleLimit = 15 * 60 * 1000;
    let posIdleTimer = null;
    let posIsLocked = Boolean(serverInitiallyLocked);
    let lastActivityPing = Date.now();
    let lastMouseActivity = 0;

    function showPosLock(notifyServer = true) {
        if (posIsLocked) return;
        posIsLocked = true;
        clearTimeout(posIdleTimer);
        posLockOverlay.classList.add('is-visible');
        posLockOverlay.setAttribute('aria-hidden', 'false');
        posLockError.textContent = '';
        posUnlockPassword.value = '';

        if (notifyServer) {
            fetch(lockUrl, {
                method: 'POST',
                headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':csrfToken }
            }).catch(() => {});
        }
        setTimeout(() => posUnlockEmail.focus(), 50);
    }

    async function pingPosActivity() {
        if (posIsLocked || (Date.now() - lastActivityPing) < 30000) return;
        lastActivityPing = Date.now();
        try {
            const response = await fetch(activityUrl, {
                method: 'POST',
                headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':csrfToken }
            });
            if (response.status === 423) showPosLock(false);
        } catch (error) {
            // Server-side expiry remains authoritative if the network is unavailable.
        }
    }

    function resetPosIdleTimer() {
        if (posIsLocked) return;
        clearTimeout(posIdleTimer);
        posIdleTimer = setTimeout(() => showPosLock(true), posIdleLimit);
        pingPosActivity();
    }

    ['pointerdown', 'keydown', 'touchstart', 'scroll'].forEach(eventName => {
        document.addEventListener(eventName, resetPosIdleTimer, { passive:true });
    });
    document.addEventListener('mousemove', () => {
        const nowMs = Date.now();
        if (nowMs - lastMouseActivity >= 1000) {
            lastMouseActivity = nowMs;
            resetPosIdleTimer();
        }
    }, { passive:true });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && !posIsLocked) resetPosIdleTimer();
    });

    posUnlockForm.addEventListener('submit', async event => {
        event.preventDefault();
        if (!posIsLocked) return;
        posUnlockButton.disabled = true;
        posUnlockButton.textContent = 'Verifying...';
        posLockError.textContent = '';

        try {
            const response = await fetch(unlockUrl, {
                method:'POST',
                headers: {
                    'Accept':'application/json', 'Content-Type':'application/json',
                    'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':csrfToken
                },
                body:JSON.stringify({ email:posUnlockEmail.value.trim(), password:posUnlockPassword.value })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'The email or password is incorrect.');

            csrfToken = data.csrf_token;
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) csrfMeta.content = csrfToken;
            posIsLocked = false;
            posLockOverlay.classList.remove('is-visible');
            posLockOverlay.setAttribute('aria-hidden', 'true');
            posUnlockPassword.value = '';
            lastActivityPing = Date.now();
            resetPosIdleTimer();
            showToast('POS unlocked. You can continue your sale.', 'success');
        } catch (error) {
            posLockError.textContent = error.message || 'Unable to unlock. Please try again.';
            posUnlockPassword.value = '';
            posUnlockPassword.focus();
        } finally {
            posUnlockButton.disabled = false;
            posUnlockButton.textContent = 'Unlock POS';
        }
    });

    if (serverInitiallyLocked) {
        posLockOverlay.classList.add('is-visible');
        posLockOverlay.setAttribute('aria-hidden', 'false');
        setTimeout(() => posUnlockEmail.focus(), 50);
    } else {
        resetPosIdleTimer();
    }

    function skeletonGrid() {
        productGrid.innerHTML = Array.from({ length: 6 }).map(() => `
            <div class="pos-skeleton">
                <div class="pos-skeleton-block" style="height:90px;margin-bottom:9px;"></div>
                <div class="pos-skeleton-block" style="height:12px;width:80%;margin-bottom:6px;"></div>
                <div class="pos-skeleton-block" style="height:10px;width:50%;"></div>
            </div>
        `).join('');
    }

    async function searchProducts() {
        const term = searchInput.value.trim();

        if (!term) {
            products = [];
            renderProducts();
            return;
        }

        skeletonGrid();

        try {
            const url = new URL(searchUrl, window.location.origin);
            url.searchParams.set('search', term);
            url.searchParams.set('branch_id', branchSelect.value);

            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (response.status === 423) {
                showPosLock(false);
                return;
            }
            if (!response.ok) throw new Error('Product search failed.');

            const data = await response.json();
            products = data.products || [];
            activeCategory = 'all';
            renderCategories();
            renderProducts();
        } catch (error) {
            productGrid.innerHTML = `
                <div class="pos-empty">
                    <i class="fas fa-triangle-exclamation"></i>
                    <div class="pos-empty-title">Could not load products</div>
                    <div class="pos-empty-sub">Please check your connection and try again.</div>
                </div>`;
        }
    }

    function renderCategories() {
        const categories = [...new Set(products.map(p => p.category).filter(Boolean))];

        categoryContainer.innerHTML =
            '<button type="button" class="pos-category active" data-category="all">All products</button>';

        categories.forEach(category => {
            categoryContainer.insertAdjacentHTML('beforeend',
                `<button type="button" class="pos-category" data-category="${escapeHtml(category)}">${escapeHtml(category)}</button>`
            );
        });
    }

    function stockBadgeClass(stock) {
        if (stock <= 0) return 'out';
        if (stock <= 5) return 'low';
        return '';
    }

    function renderProducts() {
        const filtered = activeCategory === 'all'
            ? products
            : products.filter(p => p.category === activeCategory);

        if (!filtered.length) {
            productGrid.innerHTML = `
                <div class="pos-empty">
                    <i class="fas fa-box-open"></i>
                    <div class="pos-empty-title">No matching products</div>
                    <div class="pos-empty-sub">Try a different search or category.</div>
                </div>`;
            return;
        }

        productGrid.innerHTML = filtered.map(product => {
            const image = product.image
                ? `<img src="/storage/${encodeURI(product.image)}" alt="" loading="lazy">`
                : '<i class="fas fa-box"></i>';

            const stock = Number(product.available_quantity || 0);
            const badgeClass = stockBadgeClass(stock);

            return `
                <button type="button"
                    class="pos-product-card"
                    data-variation="${Number(product.variation_id)}"
                    ${stock <= 0 ? 'disabled' : ''}
                    aria-label="${escapeHtml(product.product_name)} — ${money(product.selling_price)}">
                    <div class="pos-product-icon">
                        ${image}
                        <span class="pos-product-stock-badge ${badgeClass}">${stock} left</span>
                    </div>
                    <div class="pos-product-name">${escapeHtml(product.product_name)}</div>
                    <div class="pos-product-variation">${escapeHtml(product.variation_name || '')}</div>
                    <div class="pos-product-price">${money(product.selling_price)}</div>
                </button>
            `;
        }).join('');
    }

    function addToCart(variationId) {
        const product = products.find(p => Number(p.variation_id) === Number(variationId));
        if (!product) return;

        const existing = cart.find(i => Number(i.variation_id) === Number(variationId));

        if (existing) {
            if (existing.quantity >= Number(product.available_quantity)) {
                showToast('You cannot add more than the available branch stock.', 'error');
                return;
            }
            existing.quantity++;
        } else {
            if (Number(product.available_quantity) <= 0) return;
            cart.push({ ...product, quantity: 1 });
        }

        renderCart();
    }

    function changeQuantity(variationId, amount) {
        const item = cart.find(p => Number(p.variation_id) === Number(variationId));
        if (!item) return;

        const next = item.quantity + amount;

        if (next <= 0) {
            cart = cart.filter(p => Number(p.variation_id) !== Number(variationId));
        } else if (next > Number(item.available_quantity)) {
            showToast('Quantity exceeds available branch stock.', 'error');
            return;
        } else {
            item.quantity = next;
        }

        renderCart();
    }

    function calculateTotals() {
        const subtotal = cart.reduce(
            (sum, item) => sum + Number(item.selling_price) * item.quantity, 0
        );

        const discount = Math.min(
            Math.max(Number(discountInput.value || 0), 0),
            subtotal
        );

        let tax = 0;
        let total = 0;

        cart.forEach(item => {
            const line = Number(item.selling_price) * item.quantity;
            const lineDiscount = subtotal > 0 ? discount * (line / subtotal) : 0;
            const discountedLine = line - lineDiscount;
            const rate = Number(item.tax_rate || 0) / 100;

            if (item.tax_status === 'taxable' && rate > 0) {
                if (item.tax_inclusive) {
                    tax += discountedLine - (discountedLine / (1 + rate));
                    total += discountedLine;
                } else {
                    const lineTax = discountedLine * rate;
                    tax += lineTax;
                    total += discountedLine + lineTax;
                }
            } else {
                total += discountedLine;
            }
        });

        return { subtotal, discount, tax, total };
    }

    let completedReceipt = null;

    function renderReceiptDraft(totals) {
        document.getElementById('posReceiptNumber').textContent = 'Pending checkout';
        document.getElementById('posReceiptDate').textContent = new Date().toLocaleString();
        document.getElementById('posReceiptMethod').textContent = 'Cash';
        document.getElementById('posReceiptItems').innerHTML = cart.length
            ? cart.map(item => `
                <div class="pos-receipt-item">
                    <span class="item-name">${escapeHtml(item.product_name)}</span>
                    <span class="item-qty">${item.quantity}</span>
                    <span class="item-price">${money(item.selling_price)}</span>
                    <strong class="item-total">${money(item.selling_price * item.quantity)}</strong>
                </div>
            `).join('')
            : '<div class="pos-receipt-empty">No items added</div>';

        document.getElementById('posReceiptSubtotal').textContent = money(totals.subtotal);
        document.getElementById('posReceiptTotal').textContent = money(totals.total);
        document.getElementById('posReceiptAmountPaid').textContent = money(0);
        document.getElementById('posReceiptChange').textContent = money(0);
        document.getElementById('posReceiptStatus').textContent = 'PENDING CHECKOUT';
        printButton.disabled = true;
    }

    function renderCompletedReceipt(receipt) {
        document.getElementById('posReceiptNumber').textContent = receipt.receiptNumber;
        document.getElementById('posReceiptDate').textContent = receipt.date;
        document.getElementById('posReceiptMethod').textContent = receipt.paymentMethod || 'Cash';
        document.getElementById('posReceiptItems').innerHTML = receipt.items.map(item => `
            <div class="pos-receipt-item">
                <span class="item-name">${escapeHtml(item.product_name)}</span>
                <span class="item-qty">${item.quantity}</span>
                <span class="item-price">${money(item.selling_price)}</span>
                <strong class="item-total">${money(item.selling_price * item.quantity)}</strong>
            </div>
        `).join('');

        document.getElementById('posReceiptSubtotal').textContent = money(receipt.subtotal);
        document.getElementById('posReceiptTotal').textContent = money(receipt.total);
        document.getElementById('posReceiptAmountPaid').textContent = money(receipt.amountPaid);
        document.getElementById('posReceiptChange').textContent = money(receipt.change);
        document.getElementById('posReceiptStatus').textContent = '*** PAID IN FULL ***';
        printButton.disabled = false;
    }

    function renderCart() {
        if (!cart.length) {
            cartContainer.innerHTML = `
                <div class="pos-cart-empty">
                    <i class="fas fa-basket-shopping"></i>
                    <div class="pos-cart-empty-title">Your cart is empty</div>
                    <div class="pos-cart-empty-sub">Add products from the left to begin</div>
                </div>`;
        } else {
            cartContainer.innerHTML = cart.map(item => `
                <div class="pos-cart-item">
                    <div>
                        <div class="pos-cart-name">${escapeHtml(item.product_name)}</div>
                        <div class="pos-cart-unit">${money(item.selling_price)} each</div>
                        <div class="pos-cart-controls">
                            <button class="pos-qty-btn" data-action="minus" data-id="${item.variation_id}" aria-label="Decrease quantity">−</button>
                            <span class="pos-qty-value">${item.quantity}</span>
                            <button class="pos-qty-btn" data-action="plus" data-id="${item.variation_id}" aria-label="Increase quantity">+</button>
                            <button class="pos-remove" data-action="remove" data-id="${item.variation_id}">Remove</button>
                        </div>
                    </div>
                    <div class="pos-cart-line-total">${money(item.selling_price * item.quantity)}</div>
                </div>
            `).join('');
        }

        const totals = calculateTotals();
        const count = cart.reduce((sum, item) => sum + item.quantity, 0);

        document.getElementById('posCartCount').textContent =
            `${count} item${count === 1 ? '' : 's'}`;

        document.getElementById('posSubtotal').textContent = money(totals.subtotal);
        document.getElementById('posTax').textContent = money(totals.tax);
        document.getElementById('posTotal').textContent = money(totals.total);

        if (cart.length) {
            completedReceipt = null;
            renderReceiptDraft(totals);
        } else if (completedReceipt) {
            renderCompletedReceipt(completedReceipt);
        } else {
            renderReceiptDraft(totals);
        }

        const received = Number(amountReceivedInput.value || 0);
        const change = Math.max(received - totals.total, 0);
        changePreview.textContent = money(change);

        checkoutButton.disabled = cart.length === 0 || paymentMethod !== 'cash';
    }

    productGrid.addEventListener('click', event => {
        const card = event.target.closest('[data-variation]');
        if (card) addToCart(card.dataset.variation);
    });

    categoryContainer.addEventListener('click', event => {
        const button = event.target.closest('[data-category]');
        if (!button) return;

        activeCategory = button.dataset.category;
        categoryContainer.querySelectorAll('.pos-category')
            .forEach(item => item.classList.toggle('active', item === button));
        renderProducts();
    });

    cartContainer.addEventListener('click', event => {
        const button = event.target.closest('[data-action]');
        if (!button) return;

        const id = Number(button.dataset.id);
        const action = button.dataset.action;

        if (action === 'plus') changeQuantity(id, 1);
        if (action === 'minus') changeQuantity(id, -1);
        if (action === 'remove') {
            cart = cart.filter(item => Number(item.variation_id) !== id);
            renderCart();
        }
    });

    let searchTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(searchProducts, 300);
    });
    searchInput.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            clearTimeout(searchTimer);
            searchProducts();
        }
    });

    branchSelect.addEventListener('change', () => {
        const url = new URL(window.location.href);
        url.searchParams.set('branch_id', branchSelect.value);
        window.location.href = url.toString();
    });

    discountInput.addEventListener('input', renderCart);
    customerSelect.addEventListener('change', renderCart);
    amountReceivedInput.addEventListener('input', renderCart);

    document.getElementById('posPaymentMethods').addEventListener('click', event => {
        const button = event.target.closest('[data-method]');
        if (!button) return;

        if (button.dataset.method !== 'cash') {
            showToast('This payment method is not connected yet. Please use cash.', 'error');
            return;
        }

        paymentMethod = 'cash';
        document.querySelectorAll('.pos-payment-option').forEach(option => {
            const active = option.dataset.method === 'cash';
            option.classList.toggle('active', active);
            option.setAttribute('aria-checked', active ? 'true' : 'false');
        });

        checkoutButton.querySelector('span').textContent = 'Complete cash sale';
        checkoutNote.textContent = 'Cash checkout is available. Other payment methods need their payment confirmation setup.';
        renderCart();
    });

    printButton.addEventListener('click', () => window.print());

    checkoutButton.addEventListener('click', async () => {
        if (!cart.length || paymentMethod !== 'cash') return;

        const totals = calculateTotals();
        const amountReceived = Number(amountReceivedInput.value || 0);

        if (!Number.isFinite(amountReceived) || amountReceived < totals.total) {
            showToast('The cash amount received must be at least the sale total.', 'error');
            amountReceivedInput.focus();
            return;
        }

        const payload = {
            branch_id: Number(branchSelect.value),
            customer_id: customerSelect.value || null,
            items: cart.map(item => ({
                variation_id: Number(item.variation_id),
                quantity: Number(item.quantity)
            })),
            discount_amount: Number(discountInput.value || 0),
            payment_method: 'cash',
            amount_received: amountReceived
        };

        checkoutButton.disabled = true;
        checkoutButton.querySelector('span').textContent = 'Processing...';
        checkoutButton.querySelector('i').className = 'fas fa-spinner fa-spin';
        checkoutNote.textContent = 'Saving sale and updating branch stock...';

        try {
            const response = await fetch(checkoutUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.status === 423) {
                showPosLock(false);
                return;
            }

            if (!response.ok) {
                const errors = data.errors
                    ? Object.values(data.errors).flat().join(' ')
                    : data.message || 'Checkout failed.';
                throw new Error(errors);
            }

            showToast(`Sale ${data.receipt_number} completed · Change ${money(data.change)}`, 'success');
            completedReceipt = {
                receiptNumber: data.receipt_number,
                date: new Date().toLocaleString(undefined, {
                    year: 'numeric', month: 'short', day: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                }),
                items: cart.map(item => ({ ...item })),
                subtotal: Number(data.subtotal ?? totals.subtotal),
                discount: Number(totals.discount),
                tax: Number(data.tax ?? totals.tax),
                total: Number(data.total),
                amountPaid: Number(data.amount_paid ?? data.total),
                paymentMethod: 'Cash',
                amountReceived: amountReceived,
                change: Number(data.change)
            };


            cart = [];
            discountInput.value = '0';
            amountReceivedInput.value = '';
            renderCart();

            checkoutNote.textContent = 'Sale ' + data.receipt_number + ' completed successfully.';

            await searchProducts();
        } catch (error) {
            showToast(error.message || 'Unable to complete the sale.', 'error');
            checkoutNote.textContent = 'Checkout was not completed. Please check the message and try again.';
        } finally {
            checkoutButton.querySelector('span').textContent = 'Complete cash sale';
            checkoutButton.querySelector('i').className = 'fas fa-check-circle';
            checkoutButton.disabled = cart.length === 0;
        }
    });

    document.getElementById('posReceiptDate').textContent =
        new Date().toLocaleString(undefined, {
            year: 'numeric', month: 'short', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });

    renderCart();
});
</script>
@endsection