from pathlib import Path

path = Path("resources/views/pos/index.blade.php")

if path.exists():
    raise SystemExit("POS view already exists. No changes made.")

content = r'''@extends('layouts.app')

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
    .pos-page {
        max-width: 1600px;
        margin: 0 auto;
        padding: 18px;
        color: #172b21;
    }

    .pos-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .pos-heading h2 {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 800;
    }

    .pos-heading p {
        margin: 4px 0 0;
        color: #718078;
        font-size: .85rem;
    }

    .pos-branch-select {
        min-width: 210px;
        border: 1px solid #dce7df;
        border-radius: 10px;
        padding: 10px 13px;
        background: #fff;
        color: #173b2d;
        font-weight: 600;
    }

    .pos-workspace {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(320px, .85fr);
        gap: 18px;
        align-items: start;
    }

    .pos-panel {
        background: #fff;
        border: 1px solid #e5ece7;
        border-radius: 15px;
        box-shadow: 0 4px 18px rgba(16, 43, 32, .045);
        min-width: 0;
    }

    .pos-panel-head {
        padding: 17px 18px;
        border-bottom: 1px solid #edf1ee;
    }

    .pos-panel-head h3 {
        font-size: 1rem;
        font-weight: 800;
        margin: 0;
    }

    .pos-panel-head p {
        color: #829087;
        font-size: .78rem;
        margin: 4px 0 0;
    }

    .pos-search-wrap {
        padding: 15px 18px 8px;
    }

    .pos-search {
        width: 100%;
        border: 1px solid #dce7df;
        border-radius: 11px;
        padding: 13px 15px;
        outline: none;
        font-size: .9rem;
    }

    .pos-search:focus {
        border-color: #28634a;
        box-shadow: 0 0 0 3px rgba(40, 99, 74, .1);
    }

    .pos-category-list {
        display: flex;
        gap: 8px;
        padding: 8px 18px 15px;
        overflow-x: auto;
    }

    .pos-category {
        border: 1px solid #dfe9e2;
        background: #fff;
        color: #52665a;
        border-radius: 30px;
        padding: 7px 13px;
        white-space: nowrap;
        font-size: .76rem;
        font-weight: 700;
        cursor: pointer;
    }

    .pos-category.active {
        background: #173b2d;
        border-color: #173b2d;
        color: #fff;
    }

    .pos-product-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        padding: 0 18px 18px;
    }

    .pos-product-card {
        border: 1px solid #e7eee9;
        border-radius: 12px;
        padding: 12px;
        background: #fff;
        text-align: left;
        cursor: pointer;
        transition: .18s ease;
        min-width: 0;
    }

    .pos-product-card:hover {
        border-color: #76a68a;
        transform: translateY(-2px);
        box-shadow: 0 5px 14px rgba(16, 43, 32, .07);
    }

    .pos-product-card:disabled {
        opacity: .5;
        cursor: not-allowed;
        transform: none;
    }

    .pos-product-icon {
        height: 85px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f6f2;
        border-radius: 9px;
        color: #548268;
        font-size: 1.8rem;
        margin-bottom: 10px;
        overflow: hidden;
    }

    .pos-product-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .pos-product-name {
        font-size: .8rem;
        font-weight: 800;
        line-height: 1.35;
        min-height: 32px;
        color: #233b2d;
    }

    .pos-product-variation {
        font-size: .7rem;
        color: #87938b;
        margin-top: 3px;
    }

    .pos-product-price {
        color: #1c6543;
        font-size: .9rem;
        font-weight: 800;
        margin-top: 8px;
    }

    .pos-product-stock {
        color: #7b8980;
        font-size: .68rem;
        margin-top: 3px;
    }

    .pos-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 45px 15px;
        color: #849188;
    }

    .pos-right-column {
        display: grid;
        gap: 18px;
        min-width: 0;
    }

    .pos-customer {
        padding: 15px 18px;
        border-bottom: 1px solid #edf1ee;
    }

    .pos-customer label {
        display: block;
        font-size: .73rem;
        font-weight: 800;
        color: #66766b;
        margin-bottom: 7px;
    }

    .pos-customer select {
        width: 100%;
        border: 1px solid #dce7df;
        border-radius: 9px;
        padding: 10px;
        background: #fff;
    }

    .pos-cart-items {
        padding: 5px 18px;
        max-height: 390px;
        overflow-y: auto;
    }

    .pos-cart-empty {
        padding: 38px 10px;
        text-align: center;
        color: #87938b;
    }

    .pos-cart-empty i {
        display: block;
        font-size: 2rem;
        margin-bottom: 10px;
        color: #b8c9bd;
    }

    .pos-cart-item {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
        padding: 13px 0;
        border-bottom: 1px solid #edf1ee;
    }

    .pos-cart-item:last-child {
        border-bottom: 0;
    }

    .pos-cart-name {
        font-size: .8rem;
        font-weight: 800;
        color: #263c2e;
    }

    .pos-cart-unit {
        font-size: .72rem;
        color: #859188;
        margin-top: 3px;
    }

    .pos-cart-controls {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 9px;
    }

    .pos-qty-btn {
        width: 27px;
        height: 27px;
        border: 1px solid #dce7df;
        border-radius: 7px;
        background: #fff;
        color: #244c35;
        font-weight: 800;
    }

    .pos-qty-value {
        min-width: 20px;
        text-align: center;
        font-size: .8rem;
        font-weight: 800;
    }

    .pos-remove {
        border: 0;
        background: transparent;
        color: #c35a51;
        font-size: .75rem;
        padding: 3px;
    }

    .pos-cart-line-total {
        text-align: right;
        font-size: .82rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .pos-summary {
        padding: 15px 18px;
        border-top: 1px solid #edf1ee;
    }

    .pos-summary-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin: 8px 0;
        font-size: .8rem;
        color: #64736a;
    }

    .pos-summary-total {
        border-top: 1px dashed #dce7df;
        padding-top: 13px;
        margin-top: 13px;
        color: #173b2d;
        font-size: 1.05rem;
        font-weight: 900;
    }

    .pos-discount {
        width: 90px;
        border: 1px solid #dce7df;
        border-radius: 7px;
        padding: 5px 7px;
        text-align: right;
    }

    .pos-checkout {
        padding: 0 18px 18px;
    }

    .pos-payment-methods {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 13px;
    }

    .pos-payment-option {
        border: 1px solid #dce7df;
        border-radius: 9px;
        padding: 10px 7px;
        background: #fff;
        color: #52665a;
        font-size: .76rem;
        font-weight: 800;
        cursor: pointer;
    }

    .pos-payment-option.active {
        border-color: #28634a;
        background: #edf6ef;
        color: #1c6543;
    }

    .pos-checkout-btn {
        width: 100%;
        border: 0;
        border-radius: 10px;
        background: #173b2d;
        color: #fff;
        padding: 13px;
        font-weight: 800;
        cursor: not-allowed;
        opacity: .55;
    }

    .pos-checkout-note {
        text-align: center;
        color: #849188;
        font-size: .69rem;
        margin-top: 8px;
    }

    .pos-receipt {
        padding: 18px;
    }

    .pos-receipt-brand {
        text-align: center;
        padding-bottom: 13px;
        border-bottom: 1px dashed #dce7df;
    }

    .pos-receipt-brand strong {
        display: block;
        font-size: 1rem;
        color: #173b2d;
    }

    .pos-receipt-brand span {
        display: block;
        color: #829087;
        font-size: .72rem;
        margin-top: 3px;
    }

    .pos-receipt-details {
        padding: 12px 0;
        font-size: .73rem;
        color: #77857c;
        line-height: 1.8;
    }

    .pos-receipt-items {
        border-top: 1px dashed #dce7df;
        border-bottom: 1px dashed #dce7df;
        padding: 10px 0;
        font-size: .73rem;
    }

    .pos-receipt-item {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        margin: 7px 0;
    }

    .pos-receipt-footer {
        text-align: center;
        color: #87938b;
        font-size: .7rem;
        padding-top: 13px;
    }

    @media (max-width: 1050px) {
        .pos-workspace {
            grid-template-columns: minmax(0, 1fr);
        }

        .pos-right-column {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            align-items: start;
        }
    }

    @media (max-width: 650px) {
        .pos-page {
            padding: 10px;
        }

        .pos-right-column {
            grid-template-columns: minmax(0, 1fr);
        }

        .pos-product-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
            padding-left: 12px;
            padding-right: 12px;
        }

        .pos-panel-head,
        .pos-search-wrap,
        .pos-category-list {
            padding-left: 12px;
            padding-right: 12px;
        }

        .pos-branch-select {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="pos-page">

    <div class="pos-topbar">
        <div class="pos-heading">
            <h2><i class="fas fa-cash-register me-2"></i>Point of Sale</h2>
            <p>{{ $business->name }} · Fast and secure checkout</p>
        </div>

        <div>
            <label for="posBranch" class="small fw-bold text-muted d-block mb-1">
                Selling branch
            </label>
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

        <section class="pos-panel">
            <div class="pos-panel-head">
                <h3>Products</h3>
                <p>Search by product name, SKU or scan a barcode</p>
            </div>

            <div class="pos-search-wrap">
                <input
                    type="search"
                    id="posSearch"
                    class="pos-search"
                    placeholder="Search products or scan barcode..."
                    autocomplete="off"
                >
            </div>

            <div class="pos-category-list" id="posCategories">
                <button type="button" class="pos-category active" data-category="all">
                    All products
                </button>
            </div>

            <div class="pos-product-grid" id="posProductGrid">
                <div class="pos-empty">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <div>Search for products to begin</div>
                </div>
            </div>
        </section>

        <div class="pos-right-column">

            <section class="pos-panel">
                <div class="pos-panel-head">
                    <h3><i class="fas fa-shopping-cart me-2"></i>Current Sale</h3>
                    <p id="posCartCount">0 items</p>
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
                        Your cart is empty
                    </div>
                </div>

                <div class="pos-summary">
                    <div class="pos-summary-row">
                        <span>Subtotal</span>
                        <strong id="posSubtotal">{{ $currencySymbol }}0.00</strong>
                    </div>

                    <div class="pos-summary-row">
                        <span>Discount</span>
                        <span>
                            <input type="number" id="posDiscount"
                                class="pos-discount" value="0" min="0" step="0.01">
                        </span>
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
                    <div class="small fw-bold text-muted mb-2">Payment method</div>

                    <div class="pos-payment-methods" id="posPaymentMethods">
                        <button type="button" class="pos-payment-option active" data-method="cash">
                            <i class="fas fa-money-bill-wave me-1"></i> Cash
                        </button>
                        <button type="button" class="pos-payment-option" data-method="mobile_money">
                            <i class="fas fa-mobile-screen me-1"></i> Mobile Money
                        </button>
                        <button type="button" class="pos-payment-option" data-method="card">
                            <i class="fas fa-credit-card me-1"></i> Card
                        </button>
                        <button type="button" class="pos-payment-option" data-method="split">
                            <i class="fas fa-layer-group me-1"></i> Split payment
                        </button>
                    </div>

                    <button type="button" class="pos-checkout-btn" disabled>
                        <i class="fas fa-lock me-2"></i>Checkout
                    </button>
                    <div class="pos-checkout-note">
                        Checkout will be enabled after payment processing is connected.
                    </div>
                </div>
            </section>

            <section class="pos-panel">
                <div class="pos-panel-head">
                    <h3><i class="fas fa-receipt me-2"></i>Receipt Preview</h3>
                    <p>Live preview of the current sale</p>
                </div>

                <div class="pos-receipt">
                    <div class="pos-receipt-brand">
                        <strong>{{ $business->name }}</strong>
                        <span>{{ $selectedBranch->name }}</span>
                        <span>SALES RECEIPT</span>
                    </div>

                    <div class="pos-receipt-details">
                        <div>Receipt: Pending checkout</div>
                        <div>Date: <span id="posReceiptDate"></span></div>
                        <div>Customer: <span id="posReceiptCustomer">Walk-in Customer</span></div>
                    </div>

                    <div class="pos-receipt-items" id="posReceiptItems">
                        <div class="text-center text-muted py-3">No items added</div>
                    </div>

                    <div class="pos-receipt-details">
                        <div class="d-flex justify-content-between">
                            <span>Total</span>
                            <strong id="posReceiptTotal">{{ $currencySymbol }}0.00</strong>
                        </div>
                    </div>

                    <div class="pos-receipt-footer">
                        Thank you for your business.
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const currency = @json($currencySymbol);
    const searchUrl = @json(route('business.pos.products.search', ['business' => $business->slug]));
    const initialBranch = @json($selectedBranch->id);

    const searchInput = document.getElementById('posSearch');
    const branchSelect = document.getElementById('posBranch');
    const productGrid = document.getElementById('posProductGrid');
    const categoryContainer = document.getElementById('posCategories');
    const cartContainer = document.getElementById('posCartItems');
    const discountInput = document.getElementById('posDiscount');

    let products = [];
    let cart = [];
    let activeCategory = 'all';
    let paymentMethod = 'cash';

    const money = value => currency + Number(value || 0).toFixed(2);

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[char]);
    }

    async function searchProducts() {
        const term = searchInput.value.trim();

        if (!term) {
            products = [];
            renderProducts();
            return;
        }

        productGrid.innerHTML = '<div class="pos-empty">Searching products...</div>';

        try {
            const url = new URL(searchUrl, window.location.origin);
            url.searchParams.set('search', term);
            url.searchParams.set('branch_id', branchSelect.value);

            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Product search failed.');
            }

            const data = await response.json();
            products = data.products || [];
            activeCategory = 'all';
            renderCategories();
            renderProducts();
        } catch (error) {
            productGrid.innerHTML =
                '<div class="pos-empty text-danger">Unable to load products. Please try again.</div>';
        }
    }

    function renderCategories() {
        const categories = [...new Set(
            products.map(product => product.category).filter(Boolean)
        )];

        categoryContainer.innerHTML =
            '<button type="button" class="pos-category active" data-category="all">All products</button>';

        categories.forEach(category => {
            categoryContainer.insertAdjacentHTML('beforeend',
                `<button type="button" class="pos-category" data-category="${escapeHtml(category)}">${escapeHtml(category)}</button>`
            );
        });
    }

    function renderProducts() {
        const filtered = activeCategory === 'all'
            ? products
            : products.filter(product => product.category === activeCategory);

        if (!filtered.length) {
            productGrid.innerHTML =
                '<div class="pos-empty"><i class="fas fa-box-open fa-2x mb-2"></i><div>No matching products found</div></div>';
            return;
        }

        productGrid.innerHTML = filtered.map(product => {
            const image = product.image
                ? `<img src="/storage/${encodeURIComponent(product.image)}" alt="">`
                : '<i class="fas fa-box"></i>';

            const stock = Number(product.available_quantity || 0);

            return `
                <button type="button"
                    class="pos-product-card"
                    data-variation="${Number(product.variation_id)}"
                    ${stock <= 0 ? 'disabled' : ''}>
                    <div class="pos-product-icon">${image}</div>
                    <div class="pos-product-name">${escapeHtml(product.product_name)}</div>
                    <div class="pos-product-variation">${escapeHtml(product.variation_name || '')}</div>
                    <div class="pos-product-price">${money(product.selling_price)}</div>
                    <div class="pos-product-stock">${stock} available</div>
                </button>
            `;
        }).join('');
    }

    function addToCart(variationId) {
        const product = products.find(
            item => Number(item.variation_id) === Number(variationId)
        );

        if (!product) return;

        const existing = cart.find(
            item => Number(item.variation_id) === Number(variationId)
        );

        if (existing) {
            if (existing.quantity >= Number(product.available_quantity)) {
                alert('You cannot add more than the available branch stock.');
                return;
            }
            existing.quantity++;
        } else {
            if (Number(product.available_quantity) <= 0) return;

            cart.push({
                ...product,
                quantity: 1
            });
        }

        renderCart();
    }

    function changeQuantity(variationId, amount) {
        const item = cart.find(
            product => Number(product.variation_id) === Number(variationId)
        );

        if (!item) return;

        const next = item.quantity + amount;

        if (next <= 0) {
            cart = cart.filter(
                product => Number(product.variation_id) !== Number(variationId)
            );
        } else if (next > Number(item.available_quantity)) {
            alert('Quantity exceeds available branch stock.');
            return;
        } else {
            item.quantity = next;
        }

        renderCart();
    }

    function calculateTotals() {
        let subtotal = 0;
        let tax = 0;

        cart.forEach(item => {
            const line = Number(item.selling_price) * item.quantity;
            subtotal += line;

            const rate = Number(item.tax_rate || 0) / 100;

            if (item.tax_status === 'taxable' && rate > 0) {
                tax += item.tax_inclusive
                    ? line - (line / (1 + rate))
                    : line * rate;
            }
        });

        const discount = Math.min(
            Math.max(Number(discountInput.value || 0), 0),
            subtotal
        );

        const total = subtotal - discount +
            cart.reduce((sum, item) => {
                const line = Number(item.selling_price) * item.quantity;
                const rate = Number(item.tax_rate || 0) / 100;

                if (item.tax_status !== 'taxable' || rate <= 0) return sum;

                const lineTax = item.tax_inclusive
                    ? line - (line / (1 + rate))
                    : line * rate;

                return sum + (discount > 0 ? lineTax * ((line - discount * (line / (subtotal || 1))) / (line || 1)) : lineTax);
            }, 0);

        return { subtotal, discount, tax, total };
    }

    function renderCart() {
        if (!cart.length) {
            cartContainer.innerHTML = `
                <div class="pos-cart-empty">
                    <i class="fas fa-basket-shopping"></i>
                    Your cart is empty
                </div>`;
        } else {
            cartContainer.innerHTML = cart.map(item => `
                <div class="pos-cart-item">
                    <div>
                        <div class="pos-cart-name">${escapeHtml(item.product_name)}</div>
                        <div class="pos-cart-unit">${money(item.selling_price)} each</div>
                        <div class="pos-cart-controls">
                            <button class="pos-qty-btn" data-action="minus" data-id="${item.variation_id}">−</button>
                            <span class="pos-qty-value">${item.quantity}</span>
                            <button class="pos-qty-btn" data-action="plus" data-id="${item.variation_id}">+</button>
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
        document.getElementById('posReceiptTotal').textContent = money(totals.total);

        document.getElementById('posReceiptItems').innerHTML = cart.length
            ? cart.map(item => `
                <div class="pos-receipt-item">
                    <span>${escapeHtml(item.product_name)} × ${item.quantity}</span>
                    <strong>${money(item.selling_price * item.quantity)}</strong>
                </div>
            `).join('')
            : '<div class="text-center text-muted py-3">No items added</div>';

        document.getElementById('posReceiptCustomer').textContent =
            document.getElementById('posCustomer').selectedOptions[0].text;
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
        cart = [];
        products = [];
        searchInput.value = '';
        renderCart();
        renderProducts();

        const url = new URL(window.location.href);
        url.searchParams.set('branch_id', branchSelect.value);
        window.location.href = url.toString();
    });

    discountInput.addEventListener('input', renderCart);

    document.getElementById('posCustomer').addEventListener('change', renderCart);

    document.getElementById('posPaymentMethods').addEventListener('click', event => {
        const button = event.target.closest('[data-method]');
        if (!button) return;

        paymentMethod = button.dataset.method;

        document.querySelectorAll('.pos-payment-option').forEach(option => {
            option.classList.toggle('active', option === button);
        });
    });

    document.getElementById('posReceiptDate').textContent =
        new Date().toLocaleString();

    renderCart();
});
</script>
@endsection
'''

path.parent.mkdir(parents=True, exist_ok=True)
path.write_text(content)
print(f"Successfully created {path}")
