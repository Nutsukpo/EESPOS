<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('eespos-favicon/favicon-32.png') }}?v=2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('eespos-favicon/favicon-16.png') }}?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('eespos-favicon/apple-touch-icon.png') }}?v=2">
    <title>@yield('title', 'Log in') | EES POS</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --ees-primary: #173b2d;
            --ees-primary-light: #245641;
            --ees-accent: #e9a23b;
            --ees-text: #25332c;
            --ees-muted: #7b8981;
        }

        body {
            margin: 0;
            font-family: 'DM Sans', sans-serif;
            background: #f8fafc;
            color: var(--ees-text);
            -webkit-font-smoothing: antialiased;
        }

        .auth-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* --- Left Side: Branding --- */
        .auth-brand {
            display: none;
            width: 50%;
            background: linear-gradient(150deg, var(--ees-primary) 0%, var(--ees-primary-light) 100%);
            color: white;
            padding: 60px;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 992px) {
            .auth-brand {
                display: flex;
            }
        }

        /* Decorative background circles */
        .auth-brand::before {
            content: "";
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(233, 162, 59, 0.1);
            border-radius: 50%;
            top: -100px;
            right: -100px;
        }
        .auth-brand::after {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            z-index: 10;
        }
        .brand-icon {
            width: 48px;
            height: 48px;
            background: var(--ees-accent);
            color: var(--ees-primary);
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 24px;
            font-weight: 800;
        }
        .brand-name {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .brand-sub {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .brand-content {
            position: relative;
            z-index: 10;
            max-width: 420px;
        }
        .brand-eyebrow {
            color: var(--ees-accent);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }
        .brand-heading {
            font-size: 36px;
            line-height: 1.15;
            font-weight: 800;
            margin: 0 0 20px;
            letter-spacing: -0.5px;
        }
        .brand-text {
            font-size: 15px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.75);
            margin: 0;
        }

        .brand-footer {
            position: relative;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.5);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 24px;
        }
        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #34d399;
            border-radius: 50%;
            margin-right: 6px;
            box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.2);
        }

        /* --- Right Side: Form --- */
        .auth-form-side {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            background: #ffffff;
        }

        @media (min-width: 992px) {
            .auth-form-side {
                width: 50%;
                padding: 60px;
            }
        }

        .form-container {
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>

    <div class="auth-wrapper">
        
        <!-- LEFT: Branding -->
        <div class="auth-brand">
            <a href="{{ route('home') }}" class="brand-logo" style="text-decoration: none; color: white;">
                <div class="brand-icon">E</div>
                <div>
                    <div class="brand-name">EES POS</div>
                    <div class="brand-sub">Retail Management</div>
                </div>
            </a>

            <div class="brand-content">
                <div class="brand-eyebrow">Welcome Back</div>
                <h1 class="brand-heading">Run your shop, not your spreadsheet.</h1>
                <p class="brand-text">
                    One place for sales, stock, customers, and reports — built for retailers who want clarity, not clutter.
                </p>
            </div>

            <div class="brand-footer">
                <span>&copy; {{ date('Y') }} EES POS</span>
                <span><span class="status-dot"></span> System Online</span>
            </div>
        </div>

        <!-- RIGHT: Form Area -->
        <div class="auth-form-side">
            <div class="form-container">
                <!-- This is where the page content will be injected -->
                @yield('content')
            </div>
        </div>

    </div>

</body>
</html>