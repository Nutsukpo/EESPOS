<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') | EES POS</title>

    {{-- Core libraries --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @yield('styles')
    @stack('styles')

    <style>
        :root {
            --brand-dark: #102b20;
            --brand: #173b2d;
            --brand-light: #28634a;
            --brand-accent: #e9a23b;
            --brand-soft: #eaf3ed;

            /* Shared EES variables used by dashboard and other page views */
            --ees-primary: var(--brand);
            --ees-primary-light: var(--brand-light);
            --ees-primary-dark: var(--brand-dark);
            --ees-accent: var(--brand-accent);
            --ees-muted: var(--muted);

            --page-bg: #f5f8f6;
            --border: #e4ebe6;
            --text: #17251d;
            --muted: #718078;
            --sidebar-width: 240px;
            --navbar-height: 70px;
            --speed: .22s;
        }

        * { box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            margin: 0;
            background: var(--page-bg);
            color: var(--text);
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a { text-decoration: none; }

        /* PRELOADER */
        #preloader {
            position: fixed;
            inset: 0;
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f3f8f4, #fff);
            transition: opacity .35s ease, visibility .35s ease;
        }

        #preloader.preloader-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-container { text-align: center; }

        .loader {
            width: 58px;
            height: 58px;
            margin: 0 auto 16px;
            border: 5px solid rgba(23,59,45,.12);
            border-top-color: var(--brand);
            border-right-color: var(--brand-accent);
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }

        .loader-dots {
            display: flex;
            justify-content: center;
            gap: 7px;
            margin-bottom: 14px;
        }

        .loader-dots .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--brand);
            animation: pulse 1.2s infinite ease-in-out;
        }

        .loader-dots .dot:nth-child(2) { animation-delay: .15s; }
        .loader-dots .dot:nth-child(3) { animation-delay: .3s; }

        .loader-text {
            display: inline-block;
            padding: 7px 18px;
            border-radius: 30px;
            background: var(--brand-soft);
            color: var(--brand-dark);
            font-weight: 800;
        }

        .loader-subtext {
            margin-top: 8px;
            color: var(--muted);
            font-size: .72rem;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes pulse {
            0%,100% { opacity: .3; transform: scale(.8); }
            50% { opacity: 1; transform: scale(1); }
        }

        /* NAVBAR */
        .app-navbar {
            position: sticky;
            top: 0;
            z-index: 2000;
            min-height: var(--navbar-height);
            background: rgba(255,255,255,.97);
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(12px);
        }

        .navbar-inner {
            min-height: var(--navbar-height);
            padding: 0 1.1rem;
        }

        .sidebar-trigger {
            width: 44px;
            height: 44px;
            border: 0;
            border-radius: 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, var(--brand-dark), var(--brand-light));
            box-shadow: 0 7px 18px rgba(23,59,45,.22);
            cursor: pointer;
            transition: transform .2s ease;
        }

        .sidebar-trigger:hover { color: #fff; transform: translateY(-1px); }

        .navbar-title {
            color: var(--text);
            font-size: 1rem;
            font-weight: 800;
        }

        .date-display, .greeting-wrapper {
            padding: .42rem .8rem;
            border-radius: 30px;
            background: #f1f5f2;
            color: #52645a;
            font-size: .73rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .greeting-wrapper { display: flex; align-items: center; gap: 7px; }
        .greeting-icon { color: #e8a317; }

        .darkmode-toggle, .icon-btn {
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            color: #53645a;
            cursor: pointer;
        }

        .darkmode-toggle:hover, .icon-btn:hover {
            background: #eef3ef;
            color: var(--brand);
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--brand-dark), var(--brand-light));
            color: #fff;
            font-size: .85rem;
            font-weight: 800;
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dc3545;
            color: #fff;
            font-size: .58rem;
            font-weight: 800;
        }

        .notification-dropdown {
            width: 340px;
            padding: 0;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 18px 45px rgba(15,23,42,.14);
        }

        .notification-header {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            font-weight: 700;
        }

        .notification-item {
            padding: .8rem 1rem;
            border-bottom: 1px solid #eef2ef;
        }

        /* CUSTOM SIDEBAR - independent of Bootstrap offcanvas/collapse */
        .sidebar-shell {
            position: fixed;
            inset: 0;
            z-index: 5000;
            pointer-events: none;
            visibility: hidden;
        }

        .sidebar-shell.is-open {
            pointer-events: auto;
            visibility: visible;
        }

        .sidebar-panel {
            position: absolute;
            top: 0;
            left: 0;
            width: min(var(--sidebar-width), 92vw);
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, #102b20 0%, #173b2d 52%, #245641 100%);
            box-shadow: 18px 0 50px rgba(0,0,0,.28);
            transform: translateX(-105%);
            transition: transform var(--speed) ease;
        }

        .sidebar-shell.is-open .sidebar-panel { transform: translateX(0); }

        .sidebar-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(3,14,30,.42);
            opacity: 0;
            transition: opacity var(--speed) ease;
        }

        .sidebar-shell.is-open .sidebar-backdrop { opacity: 1; }

        .sidebar-head {
            flex: 0 0 auto;
            min-height: 82px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,.09);
            background: rgba(0,0,0,.12);
        }

        .sidebar-brand { display: flex; align-items: center; gap: 11px; min-width: 0; }

        .sidebar-brand-icon {
            width: 45px;
            height: 45px;
            flex: 0 0 45px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-dark);
            background: linear-gradient(135deg, #f6bd62, var(--brand-accent));
            box-shadow: 0 7px 20px rgba(0,0,0,.18);
            font-size: 1.35rem;
            font-weight: 900;
        }

        .sidebar-brand-text h5 {
            margin: 0;
            color: #fff;
            font-size: 1rem;
            font-weight: 800;
        }

        .sidebar-brand-text h5 span { color: #f6bd62; }

        .sidebar-brand-text small {
            display: block;
            margin-top: 3px;
            color: rgba(255,255,255,.58);
            font-size: .57rem;
        }

        .sidebar-close {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,.75);
            background: rgba(255,255,255,.06);
            cursor: pointer;
        }

        .sidebar-close:hover { color: #fff; background: rgba(255,255,255,.12); }

        .sidebar-content {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: .9rem .8rem 1rem;
        }

        .sidebar-content::-webkit-scrollbar { width: 5px; }
        .sidebar-content::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.17);
            border-radius: 10px;
        }

        .nav-section { margin-bottom: .85rem; }

        .nav-section-title {
            padding: .25rem .7rem .4rem;
            color: rgba(255,255,255,.42);
            font-size: .57rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .12em;
        }

        .nav-link-main, .nav-link-child {
            width: 100%;
            border: 0;
            display: flex;
            align-items: center;
            gap: 11px;
            color: rgba(255,255,255,.78);
            background: transparent;
            cursor: pointer;
            text-align: left;
        }

        .nav-link-main {
            min-height: 44px;
            padding: .65rem .78rem;
            border-radius: 12px;
            font-size: .8rem;
            font-weight: 600;
        }

        .nav-link-main:hover { color: #fff; background: rgba(255,255,255,.09); }

        .nav-link-main.active {
            color: #fff;
            background: linear-gradient(90deg, rgba(233,162,59,.25), rgba(255,255,255,.08));
            border-left: 3px solid var(--brand-accent);
        }

        .nav-link-main > i:first-child, .nav-link-child > i:first-child {
            width: 21px;
            text-align: center;
            color: rgba(255,255,255,.58);
        }

        .nav-link-main:hover > i:first-child,
        .nav-link-main.active > i:first-child { color: #f6bd62; }

        .nav-chevron {
            margin-left: auto;
            font-size: .65rem;
            transition: transform .18s ease;
        }

        .nav-group.is-expanded > .nav-link-main .nav-chevron { transform: rotate(180deg); }

        .nav-children {
            display: none;
            margin: .15rem 0 0 .72rem;
            padding-left: .55rem;
            border-left: 1px solid rgba(255,255,255,.10);
        }

        .nav-group.is-expanded > .nav-children { display: block; }

        .nav-link-child {
            min-height: 38px;
            padding: .48rem .65rem;
            border-radius: 0 9px 9px 0;
            font-size: .75rem;
        }

        .nav-link-child:hover { color: #fff; background: rgba(255,255,255,.07); }

        .nav-link-child.active {
            color: #fff;
            background: rgba(233,162,59,.14);
            border-left: 2px solid var(--brand-accent);
        }

        .nav-soon {
            margin-left: auto;
            color: rgba(255,255,255,.42);
            font-size: .55rem;
            letter-spacing: .06em;
        }

        .sidebar-user {
            flex: 0 0 auto;
            padding: .8rem .95rem;
            border-top: 1px solid rgba(255,255,255,.08);
            background: rgba(0,0,0,.17);
        }

        .sidebar-user-inner { display: flex; align-items: center; gap: 10px; }

        .sidebar-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-dark);
            background: linear-gradient(135deg, #f6bd62, var(--brand-accent));
            font-size: .78rem;
            font-weight: 800;
        }

        .sidebar-user-name { margin: 0; color: #fff; font-size: .76rem; font-weight: 700; }
        .sidebar-user-role { margin: 2px 0 0; color: rgba(255,255,255,.5); font-size: .58rem; }

        /* CONTENT / FOOTER */
        .app-content {
            width: 100%;
            min-height: calc(100vh - var(--navbar-height));
            padding: 1.25rem;
        }

        .footer {
            padding: 1rem 1.4rem;
            background: #fff;
            border-top: 1px solid var(--border);
        }

        .footer-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* LOGOUT CONFIRMATION */
        .logout-modal {
            position: fixed;
            inset: 0;
            z-index: 1000000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(3,14,30,.66);
            backdrop-filter: blur(7px);
            opacity: 0;
            visibility: hidden;
            transition: opacity .2s ease, visibility .2s ease;
        }

        .logout-modal.is-visible { opacity: 1; visibility: visible; }

        .logout-card {
            width: 100%;
            max-width: 420px;
            padding: 30px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: #fff;
            text-align: center;
            box-shadow: 0 25px 80px rgba(0,0,0,.25);
            transform: translateY(15px) scale(.98);
            transition: transform .2s ease;
        }

        .logout-modal.is-visible .logout-card { transform: translateY(0) scale(1); }

        .logout-icon {
            width: 62px;
            height: 62px;
            margin: 0 auto 17px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #fff1ed;
            color: #b42318;
            font-size: 1.45rem;
        }

        .logout-card h2 { margin: 0 0 9px; color: #17251d; font-size: 1.35rem; font-weight: 800; }
        .logout-card p { margin: 0 auto 24px; max-width: 310px; color: #718078; font-size: .82rem; line-height: 1.65; }
        .logout-actions { display: flex; gap: 11px; }

        .logout-cancel, .logout-confirm {
            flex: 1;
            min-height: 45px;
            border-radius: 9px;
            font-family: inherit;
            font-size: .8rem;
            font-weight: 700;
            cursor: pointer;
        }

        .logout-cancel { border: 1px solid #dfe8e2; background: #f7f9f7; color: #405348; }
        .logout-cancel:hover { background: #edf2ee; }
        .logout-confirm { border: 1px solid #b42318; background: #b42318; color: #fff; }
        .logout-confirm:hover { background: #941d14; }
        .logout-confirm:disabled { opacity: .65; cursor: wait; }

        .logout-overlay {
            position: fixed;
            inset: 0;
            z-index: 1000001;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 18px;
            background: rgba(0,0,0,.82);
            backdrop-filter: blur(7px);
        }

        .logout-spinner {
            width: 52px;
            height: 52px;
            border: 4px solid rgba(255,255,255,.2);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        .logout-text { color: #fff; font-size: 1rem; font-weight: 600; }

        /* DARK MODE */
        body.dark-mode {
            --page-bg: #0f172a;
            --text: #e5edf7;
            --muted: #94a3b8;
            --border: #26344a;
            background: #0f172a;
            color: #e5edf7;
        }

        body.dark-mode .app-navbar, body.dark-mode .footer {
            background: #111b2d;
            border-color: #26344a;
        }

        body.dark-mode .navbar-title,
        body.dark-mode .darkmode-toggle,
        body.dark-mode .icon-btn { color: #e5edf7; }

        body.dark-mode .date-display,
        body.dark-mode .greeting-wrapper { background: #1e293b; color: #cbd5e1; }

        body.dark-mode .dropdown-menu { background: #172033; border-color: #26344a; }
        body.dark-mode .dropdown-item { color: #e5edf7; }
        body.dark-mode .dropdown-item:hover { background: #26344a; }

        @media (max-width: 991.98px) {
            :root { --navbar-height: 64px; --sidebar-width: 260px; }
            .navbar-inner { min-height: 64px; }
            .greeting-text, .date-display { display: none; }
            .app-content { padding: 1rem; }
        }

        @media (max-width: 575.98px) {
            :root { --sidebar-width: min(260px, 88vw); }
            .app-content { padding: .75rem; }
            .navbar-title {
                max-width: 130px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .notification-dropdown { width: min(340px, calc(100vw - 20px)); }
            .sidebar-trigger, .darkmode-toggle, .icon-btn, .user-avatar {
                width: 39px;
                height: 39px;
            }
            .logout-actions { flex-direction: column-reverse; }
            .logout-card { padding: 25px 21px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                animation-duration: .01ms !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>

<body class="preloader-active">

    {{-- PRELOADER --}}
    <div id="preloader" aria-hidden="true">
        <div class="loader-container">
            <div class="loader"></div>
            <div class="loader-dots">
                <span class="dot"></span><span class="dot"></span><span class="dot"></span>
            </div>
            <div class="loader-text">EES POS</div>
            <div class="loader-subtext">Loading at {{ now()->format('h:i:s A') }} ...</div>
        </div>
    </div>

    @auth
        {{-- CUSTOM SIDEBAR --}}
        <aside class="sidebar-shell" id="sidebarShell" aria-hidden="true">
            <div class="sidebar-backdrop" aria-hidden="true"></div>

            <div class="sidebar-panel" id="sidebarPanel">
                <div class="sidebar-head">
                    <div class="sidebar-brand">
                        <div class="sidebar-brand-icon"><i class="fas fa-store"></i></div>
                        <div class="sidebar-brand-text">
                            <h5>EES <span>POS</span></h5>
                            <small>Enterprise Business Management</small>
                        </div>
                    </div>

                    <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Close navigation">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="sidebar-content">
                    <div class="nav-section">
                        <div class="nav-section-title">Workspace</div>
                        <a href="{{ route('dashboard') }}"
                           class="nav-link-main {{ request()->routeIs('dashboard') || request()->routeIs('business.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-gauge-high"></i><span>Dashboard</span>
                        </a>
                        @if(request()->attributes->get('current_business'))
                            <a href="{{ route('business.profile.show', ['business' => request()->attributes->get('current_business')->slug]) }}"
                               class="nav-link-main {{ request()->routeIs('business.profile.*') ? 'active' : '' }}">
                                <i class="fas fa-building"></i><span>Business Profile</span>
                            </a>
                        @else
                            <a href="{{ route('business.create') }}"
                               class="nav-link-main {{ request()->routeIs('business.create') ? 'active' : '' }}">
                                <i class="fas fa-building"></i><span>Business Profile</span>
                            </a>
                        @endif
                    </div>

                    @if(request()->attributes->get('current_business'))                    <div class="nav-section nav-group" data-menu-group="sales">
                        <button type="button" class="nav-link-main nav-toggle">
                            <i class="fas fa-cash-register"></i><span>Sales & POS</span>
                            <i class="fas fa-chevron-down nav-chevron"></i>
                        </button>
                        <div class="nav-children">
                            <a href="{{ route('business.pos.index', ['business' => request()->route('business')->slug]) }}"
                               class="nav-link-child {{ request()->routeIs('business.pos.*') ? 'active' : '' }}">
                                <i class="fas fa-cart-shopping"></i><span>Point of Sale</span>
                            </a>
                            @if(auth()->user()->can('sales.view.all') || auth()->user()->can('sales.view.own'))
                            <a href="{{ route('business.sales.index', ['business' => request()->attributes->get('current_business')->slug]) }}"
                               class="nav-link-child {{ request()->routeIs('business.sales.*') ? 'active' : '' }}">
                                <i class="fas fa-receipt"></i><span>Sales History</span>
                            </a>
                            @endif
                            <a href="#" class="nav-link-child"><i class="fas fa-rotate-left"></i><span>Sales Returns</span><span class="nav-soon">SOON</span></a>
                        </div>
                    </div>
                    @endif

                    <div class="nav-section nav-group" data-menu-group="inventory">
                        <button type="button" class="nav-link-main nav-toggle">
                            <i class="fas fa-boxes-stacked"></i><span>Inventory</span>
                            <i class="fas fa-chevron-down nav-chevron"></i>
                        </button>
                        <div class="nav-children">
                            @if(request()->attributes->get('current_business') && auth()->user()?->can('products.view'))
                                <a href="{{ route('business.products.index', ['business' => request()->attributes->get('current_business')->slug]) }}"
                                   class="nav-link-child {{ request()->routeIs('business.products.*') ? 'active' : '' }}">
                                    <i class="fas fa-box"></i>
                                    <span>Products</span>
                                </a>
                            @endif
                            @if(request()->attributes->get('current_business') && auth()->user()?->can('categories.view'))
                                <a href="{{ route('business.categories.index', ['business' => request()->attributes->get('current_business')->slug]) }}"
                                   class="nav-link-child {{ request()->routeIs('business.categories.*') ? 'active' : '' }}">
                                    <i class="fas fa-layer-group"></i>
                                    <span>Categories</span>
                                </a>
                            @endif
                            @if(request()->attributes->get('current_business') && auth()->user()?->can('inventory.view'))
                                <a href="{{ route('business.stock.index', ['business' => request()->attributes->get('current_business')->slug]) }}"
                                   class="nav-link-child {{ request()->routeIs('business.stock.*') ? 'active' : '' }}">
                                    <i class="fas fa-warehouse"></i>
                                    <span>Stock Management</span>
                                </a>
                            @endif
                            <a href="#" class="nav-link-child"><i class="fas fa-triangle-exclamation"></i><span>Low Stock</span><span class="nav-soon">SOON</span></a>
                        </div>
                    </div>

                    <div class="nav-section nav-group" data-menu-group="contacts">
                        <button type="button" class="nav-link-main nav-toggle">
                            <i class="fas fa-address-book"></i><span>Contacts</span>
                            <i class="fas fa-chevron-down nav-chevron"></i>
                        </button>
                        <div class="nav-children">
                            <a href="#" class="nav-link-child"><i class="fas fa-users"></i><span>Customers</span><span class="nav-soon">SOON</span></a>
                            <a href="#" class="nav-link-child"><i class="fas fa-truck"></i><span>Suppliers</span><span class="nav-soon">SOON</span></a>
                        </div>
                    </div>

                    <div class="nav-section nav-group" data-menu-group="finance">
                        <button type="button" class="nav-link-main nav-toggle">
                            <i class="fas fa-wallet"></i><span>Finance</span>
                            <i class="fas fa-chevron-down nav-chevron"></i>
                        </button>
                        <div class="nav-children">
                            <a href="#" class="nav-link-child"><i class="fas fa-money-bill-transfer"></i><span>Expenses</span><span class="nav-soon">SOON</span></a>
                            <a href="#" class="nav-link-child"><i class="fas fa-chart-line"></i><span>Revenue</span><span class="nav-soon">SOON</span></a>
                            <a href="#" class="nav-link-child"><i class="fas fa-file-invoice-dollar"></i><span>Financial Reports</span><span class="nav-soon">SOON</span></a>
                        </div>
                    </div>

                    <div class="nav-section nav-group" data-menu-group="administration">
                        <button type="button" class="nav-link-main nav-toggle">
                            <i class="fas fa-users-gear"></i><span>Administration</span>
                            <i class="fas fa-chevron-down nav-chevron"></i>
                        </button>
                        <div class="nav-children">
                            @if(request()->attributes->get('can_view_business_users', false) && request()->attributes->get('current_business'))
                                <a href="{{ route('business.users.index', ['business' => request()->attributes->get('current_business')->slug]) }}"
                                   class="nav-link-child {{ request()->routeIs('business.users.*') ? 'active' : '' }}">
                                    <i class="fas fa-user-tie"></i>
                                    <span>Users Management</span>
                                </a>
                            @endif
                            <a href="#" class="nav-link-child"><i class="fas fa-user-shield"></i><span>Roles & Permissions</span><span class="nav-soon">SOON</span></a>
                            <a href="#" class="nav-link-child"><i class="fas fa-sliders"></i><span>Settings</span><span class="nav-soon">SOON</span></a>
                        </div>
                    </div>

                    <div class="nav-section">
                        <div class="nav-section-title">Support</div>
                        <a href="#" class="nav-link-main"><i class="fas fa-circle-question"></i><span>Help & Support</span><span class="nav-soon">SOON</span></a>
                    </div>
                </div>

                <div class="sidebar-user">
                    <div class="sidebar-user-inner">
                        <div class="sidebar-user-avatar" id="sidebarUserInitials">EE</div>
                        <div>
                            <p class="sidebar-user-name" id="sidebarUserName">{{ auth()->user()->name }}</p>
                            <p class="sidebar-user-role">EES POS User</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- NAVBAR --}}
        <nav class="navbar app-navbar">
            <div class="container-fluid navbar-inner">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="sidebar-trigger" id="sidebarTrigger"
                            aria-label="Open navigation" aria-controls="sidebarShell" aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </button>

                    <a href="{{ route('dashboard') }}" class="navbar-title">
                        @yield('page-heading', 'Business Dashboard')
                    </a>

                    <div class="date-display">
                        <i class="far fa-calendar-alt me-1"></i><span id="liveDateTime"></span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 ms-auto">
                    <div class="greeting-wrapper">
                        <i class="fas fa-sun greeting-icon" id="greetingIcon"></i>
                        <span class="greeting-text" id="greetingMessage">Welcome</span>
                    </div>

                    <button type="button" class="darkmode-toggle" id="darkModeToggle" aria-label="Toggle dark mode">
                        <i class="fas fa-moon" id="darkModeIcon"></i>
                    </button>

                    {{-- Notifications placeholder until notification records are connected --}}
                    <div class="dropdown">
                        <button type="button" class="icon-btn position-relative"
                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                            <i class="fas fa-bell fs-5"></i>
                            <span class="notification-badge" id="notificationCount" style="display:none">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                            <div class="notification-header">
                                <i class="fas fa-bell me-2"></i>Notifications
                                <span class="float-end text-primary small" style="cursor:pointer" id="markAllReadBtn">Mark all read</span>
                            </div>
                            <div id="notificationList">
                                <div class="text-center py-4 text-muted small">
                                    <i class="far fa-bell-slash d-block fs-4 mb-2"></i>
                                    No new notifications
                                </div>
                            </div>
                            <div class="text-center p-2">
                                <span class="small text-muted">You're all caught up</span>
                            </div>
                        </div>
                    </div>

                    {{-- User menu --}}
                    <div class="dropdown">
                        <button type="button" class="nav-link p-0 border-0 bg-transparent"
                                data-bs-toggle="dropdown" aria-expanded="false" aria-label="User menu">
                            <div class="user-avatar" id="userAvatarInitials">
                                {{ auth()->user()->initials() }}
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li class="dropdown-header text-muted small px-4 py-2">
                                <i class="fas fa-user-circle me-1"></i>
                                <span id="userNameHeader">{{ auth()->user()->name }}</span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="button" class="dropdown-item" disabled>
                                    <i class="fas fa-user me-2"></i>My Profile
                                    <small class="text-muted ms-1">Soon</small>
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item" disabled>
                                    <i class="fas fa-gear me-2"></i>Settings
                                    <small class="text-muted ms-1">Soon</small>
                                </button>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="button" class="dropdown-item text-danger" id="logoutBtn">
                                    <i class="fas fa-right-from-bracket me-2"></i>Logout
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        {{-- PAGE CONTENT --}}
        <main class="app-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <strong>Please correct the following:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
            {{ $slot ?? '' }}
        </main>

        {{-- FOOTER --}}
        <footer class="footer">
            <div class="footer-content">
                <div class="small text-muted">&copy; {{ date('Y') }} EES POS. All rights reserved.</div>
                <div><span id="footerGreeting" class="text-muted small"></span></div>
            </div>
        </footer>

        {{-- LOGOUT CONFIRMATION --}}
        <div class="logout-modal" id="logoutModal" role="dialog" aria-modal="true"
             aria-labelledby="logoutModalTitle" aria-describedby="logoutModalMessage" aria-hidden="true">
            <div class="logout-card">
                <div class="logout-icon"><i class="fas fa-right-from-bracket"></i></div>
                <h2 id="logoutModalTitle">Are you sure?</h2>
                <p id="logoutModalMessage">
                    Do you really want to log out of EES POS?
                    You will need to sign in again to access your business dashboard.
                </p>
                <div class="logout-actions">
                    <button type="button" class="logout-cancel" id="cancelLogout">No, stay logged in</button>
                    <button type="button" class="logout-confirm" id="confirmLogout">Yes, log me out</button>
                </div>
            </div>
        </div>

    @else
        {{-- Guest pages such as login and registration --}}
        <main class="min-vh-100">
            @yield('content')
            {{ $slot ?? '' }}
        </main>
    @endauth

    {{-- Libraries --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        (() => {
            'use strict';

            /* PRELOADER */
            const preloader = document.getElementById('preloader');

            function hidePreloader() {
                if (!preloader) return;
                preloader.classList.add('preloader-hidden');
                document.body.classList.remove('preloader-active');
                window.setTimeout(() => {
                    if (preloader) preloader.style.display = 'none';
                }, 400);
            }

            window.addEventListener('load', () => window.setTimeout(hidePreloader, 250), { once: true });
            window.setTimeout(hidePreloader, 2500);

            @auth
            /* SIDEBAR: only explicit close controls close the drawer.
               Dropdown clicks do not close the drawer. Backdrop is visual only. */
            const sidebarShell = document.getElementById('sidebarShell');
            const sidebarTrigger = document.getElementById('sidebarTrigger');
            const sidebarClose = document.getElementById('sidebarClose');

            function openSidebar() {
                if (!sidebarShell) return;
                sidebarShell.classList.add('is-open');
                sidebarShell.setAttribute('aria-hidden', 'false');
                sidebarTrigger?.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                if (!sidebarShell) return;
                sidebarShell.classList.remove('is-open');
                sidebarShell.setAttribute('aria-hidden', 'true');
                sidebarTrigger?.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }

            sidebarTrigger?.addEventListener('click', event => {
                event.preventDefault();
                if (sidebarShell?.classList.contains('is-open')) closeSidebar();
                else openSidebar();
            });

            sidebarClose?.addEventListener('click', event => {
                event.preventDefault();
                closeSidebar();
            });

            /* Sidebar dropdowns are controlled independently. */
            document.querySelectorAll('.nav-toggle').forEach(toggle => {
                toggle.addEventListener('click', event => {
                    event.preventDefault();
                    event.stopPropagation();
                    toggle.closest('.nav-group')?.classList.toggle('is-expanded');
                });
            });

            /* Active child links and automatically expand their parent group. */
            const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';

            document.querySelectorAll('.nav-link-child[href]').forEach(link => {
                const href = link.getAttribute('href');
                if (!href || href === '#') return;

                try {
                    const linkPath = new URL(href, window.location.origin).pathname.replace(/\/+$/, '') || '/';
                    if (currentPath === linkPath || (linkPath !== '/' && currentPath.startsWith(linkPath + '/'))) {
                        link.classList.add('active');
                        link.closest('.nav-group')?.classList.add('is-expanded');
                    }
                } catch (error) {
                    // Ignore invalid placeholder links.
                }
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && sidebarShell?.classList.contains('is-open')) {
                    closeSidebar();
                }
            });

            /* LOGOUT: show confirmation first, then submit Laravel's POST logout route. */
            const logoutModal = document.getElementById('logoutModal');
            const logoutBtn = document.getElementById('logoutBtn');
            const cancelLogout = document.getElementById('cancelLogout');
            const confirmLogout = document.getElementById('confirmLogout');

            function showLogoutModal() {
                if (!logoutModal) return;
                logoutModal.classList.add('is-visible');
                logoutModal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                cancelLogout?.focus();
            }

            function hideLogoutModal() {
                if (!logoutModal) return;
                logoutModal.classList.remove('is-visible');
                logoutModal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                logoutBtn?.focus();
            }

            logoutBtn?.addEventListener('click', event => {
                event.preventDefault();
                showLogoutModal();
            });

            cancelLogout?.addEventListener('click', hideLogoutModal);

            logoutModal?.addEventListener('click', event => {
                if (event.target === logoutModal) hideLogoutModal();
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && logoutModal?.classList.contains('is-visible')) {
                    hideLogoutModal();
                }
            });

            confirmLogout?.addEventListener('click', () => {
                if (confirmLogout.disabled) return;

                confirmLogout.disabled = true;
                confirmLogout.textContent = 'Logging out...';

                const overlay = document.createElement('div');
                overlay.className = 'logout-overlay';
                overlay.innerHTML = `
                    <div class="logout-spinner"></div>
                    <div class="logout-text">Logging out, please wait...</div>
                `;
                document.body.appendChild(overlay);

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = @json(route('logout'));
                form.style.display = 'none';

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]')?.content || '';

                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            });

            /* LIVE DATE / TIME */
            function updateDateTime() {
                const element = document.getElementById('liveDateTime');
                if (!element) return;

                element.textContent = new Date().toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                }).replace(',', ' |');
            }

            updateDateTime();
            window.setInterval(updateDateTime, 60000);

            /* GREETING AND USER INITIALS */
            const userNameFromBlade = @json(Auth::user()->name ?? 'User');
            const displayUserName = userNameFromBlade || 'User';

            function getGreeting() {
                const hour = new Date().getHours();
                if (hour < 12) return { text: 'Good Morning', icon: 'fa-sun', emoji: '🌅' };
                if (hour < 18) return { text: 'Good Afternoon', icon: 'fa-cloud-sun', emoji: '☀️' };
                return { text: 'Good Evening', icon: 'fa-moon', emoji: '🌙' };
            }

            function updateGreetings() {
                const greeting = getGreeting();
                const message = document.getElementById('greetingMessage');
                const icon = document.getElementById('greetingIcon');
                const footer = document.getElementById('footerGreeting');
                const avatar = document.getElementById('userAvatarInitials');
                const sidebarAvatar = document.getElementById('sidebarUserInitials');
                const headerName = document.getElementById('userNameHeader');
                const sidebarName = document.getElementById('sidebarUserName');

                if (message) message.textContent = `${greeting.text}, ${displayUserName}! ${greeting.emoji}`;
                if (icon) icon.className = `fas ${greeting.icon} greeting-icon`;
                if (footer) footer.textContent = `Have a great ${greeting.text.toLowerCase()}!`;

                const initials = displayUserName.trim().split(/\s+/)
                    .map(part => part.charAt(0)).join('').toUpperCase().substring(0, 2) || 'EE';

                if (avatar) avatar.textContent = initials;
                if (sidebarAvatar) sidebarAvatar.textContent = initials;
                if (headerName) headerName.textContent = displayUserName;
                if (sidebarName) sidebarName.textContent = displayUserName;
            }

            updateGreetings();
            window.setInterval(updateGreetings, 60000);

            /* DARK MODE */
            function initDarkMode() {
                const toggle = document.getElementById('darkModeToggle');
                const icon = document.getElementById('darkModeIcon');
                if (!toggle || !icon) return;

                const isDark = localStorage.getItem('eesDarkMode') === 'true';
                if (isDark) {
                    document.body.classList.add('dark-mode');
                    icon.classList.replace('fa-moon', 'fa-sun');
                }

                toggle.addEventListener('click', () => {
                    document.body.classList.toggle('dark-mode');
                    const darkActive = document.body.classList.contains('dark-mode');
                    localStorage.setItem('eesDarkMode', String(darkActive));
                    icon.classList.toggle('fa-moon', !darkActive);
                    icon.classList.toggle('fa-sun', darkActive);
                });
            }

            initDarkMode();

            /* NOTIFICATIONS PLACEHOLDER INTERACTION */
            document.getElementById('markAllReadBtn')?.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();

                const list = document.getElementById('notificationList');
                const count = document.getElementById('notificationCount');

                if (list) {
                    list.innerHTML = `
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-check-circle me-2"></i>All caught up!
                        </div>
                    `;
                }
                if (count) {
                    count.textContent = '0';
                    count.style.display = 'none';
                }
            });
            @endauth
        })();
    </script>

    @stack('scripts')
</body>
</html>
