
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In | EES POS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --green: #153d2d;
            --green2: #245a40;
            --gold: #efa83d;
            --ink: #17271e;
            --muted: #819087;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'DM Sans', sans-serif;
            background: #f4f7f4;
            color: var(--ink);
        }

        /* ======================================
           MAIN LAYOUT
        ====================================== */

        .login-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.08fr .92fr;
        }

        /* ======================================
           SUPERMARKET PANEL
        ====================================== */

        .visual-panel {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            isolation: isolate;
            background: #173d2d;
            color: white;
        }

        .store-scene {
            position: absolute;
            inset: 0;
            overflow: hidden;
            background:
                linear-gradient(180deg, #e9f1e8 0%, #c3d9c4 48%, #8cad8d 100%);
        }

        /* Store ceiling */
        .ceiling {
            position: absolute;
            inset: 0 0 auto;
            height: 15%;
            background: linear-gradient(180deg, #fbfaf0, #e2e8d9);
            border-bottom: 9px solid #b4c8b5;
        }

        .ceiling-light {
            position: absolute;
            top: 5%;
            left: 15%;
            width: 25%;
            height: 8px;
            border-radius: 20px;
            background: white;
            box-shadow: 0 0 30px 13px rgba(255,255,255,.65);
        }

        .ceiling-light.two {
            left: 61%;
        }

        .ceiling-light.three {
            left: 42%;
            width: 12%;
        }

        .back-wall {
            position: absolute;
            top: 15%;
            left: 0;
            right: 0;
            height: 53%;
            background:
                radial-gradient(ellipse at center, rgba(255,255,255,.25), transparent 70%),
                linear-gradient(90deg, #a9c7ad, #c6dbc7 50%, #a9c7ad);
        }

        /* Store sign */
        .store-sign {
            position: absolute;
            z-index: 3;
            top: 19%;
            left: 50%;
            transform: translateX(-50%);
            padding: 11px 27px;
            border-radius: 5px;
            background: var(--green);
            color: white;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 3px;
            white-space: nowrap;
            box-shadow: 0 8px 20px rgba(0,0,0,.2);
        }

        .store-sign span {
            color: var(--gold);
        }

        /* ======================================
           REAL GROCERY SHELVES
        ====================================== */

        .shelf {
            position: absolute;
            z-index: 2;
            top: 31%;
            width: 29%;
            height: 39%;
            padding: 8px 8px 7px;
            border: 8px solid #65472f;
            border-top: 12px solid #4b3527;
            border-radius: 4px;
            background: #b7c7ae;
            box-shadow: 0 14px 25px rgba(28,52,34,.22);
        }

        .shelf-left {
            left: 3.5%;
        }

        .shelf-right {
            right: 3.5%;
        }

        .shelf-label {
            position: absolute;
            top: -29px;
            left: 0;
            color: #294b35;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.5px;
        }

        .shelf-level {
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            gap: 5px;
            height: 33.33%;
            padding: 5px 2px 7px;
            border-bottom: 6px solid #735239;
        }

        .grocery {
            position: relative;
            display: block;
            flex: 0 1 auto;
            width: 17%;
            box-shadow: inset -4px 0 rgba(0,0,0,.10);
        }

        /* Rice, flour and cereal bags */
        .bag {
            height: 76%;
            border-radius: 5px 5px 8px 8px;
            clip-path: polygon(13% 0, 87% 0, 100% 100%, 0 100%);
            background: #e9d9a6;
        }

        .bag::before {
            content: "";
            position: absolute;
            top: 22%;
            left: 14%;
            width: 72%;
            height: 28%;
            background: rgba(255,255,255,.55);
            border-radius: 3px;
        }

        .bag.rice { background: #e7d6a5; }
        .bag.flour { background: #e9e0c9; }
        .bag.maize { background: #e6bd55; }
        .bag.beans { background: #b9784e; }

        /* Cooking oil and drinks */
        .bottle {
            height: 87%;
            width: 13%;
            border-radius: 5px 5px 4px 4px;
            background: #dcae36;
        }

        .bottle::before {
            content: "";
            position: absolute;
            top: -14%;
            left: 32%;
            width: 36%;
            height: 17%;
            border-radius: 3px 3px 0 0;
            background: #e2b94e;
        }

        .bottle.oil { background: #e9b934; }
        .bottle.water { background: #74b8d1; }
        .bottle.juice { background: #dc753c; }
        .bottle.soda { background: #b94e43; }

        /* Tinned foods */
        .tin {
            height: 66%;
            border-radius: 4px 4px 3px 3px;
            background: #d65b4b;
            border-top: 4px solid #a9a9a0;
        }

        .tin::after {
            content: "";
            position: absolute;
            left: 12%;
            right: 12%;
            top: 36%;
            height: 18%;
            background: rgba(255,255,255,.65);
            border-radius: 2px;
        }

        .tin.green { background: #5b8d60; }
        .tin.blue { background: #558eaa; }
        .tin.yellow { background: #d5ae46; }

        /* Cereal and packaged provisions */
        .box {
            height: 82%;
            border-radius: 3px;
            background: #d96b42;
        }

        .box::before {
            content: "";
            position: absolute;
            left: 10%;
            right: 10%;
            top: 18%;
            height: 30%;
            border-radius: 3px;
            background: rgba(255,255,255,.5);
        }

        .box.cereal { background: #e6b94d; }
        .box.pasta { background: #d47b42; }
        .box.green { background: #719d64; }

        /* Bread */
        .bread {
            height: 55%;
            border-radius: 45% 45% 7px 7px;
            background: #c88a4d;
            border-top: 5px solid #e5b879;
        }

        .bread::after {
            content: "";
            position: absolute;
            top: 25%;
            left: 25%;
            width: 50%;
            height: 3px;
            border-radius: 4px;
            background: rgba(255,255,255,.45);
        }

        /* Jars */
        .jar {
            height: 67%;
            border-radius: 5px 5px 7px 7px;
            background: #d9a34b;
            border-top: 6px solid #795c37;
        }

        .jar.tomato { background: #c85d42; }
        .jar.honey { background: #dca83c; }
        .jar.peanut { background: #ae794a; }

        /* Small fresh produce baskets */
        .produce-basket {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 1px;
            width: 22%;
            height: 55%;
            padding-bottom: 5px;
            border-radius: 3px;
            background: #9a6742;
        }

        .produce-basket span {
            width: 8px;
            height: 13px;
            border-radius: 50%;
            background: #d74f40;
        }

        .produce-basket span:nth-child(2) {
            background: #e8b33c;
            transform: translateY(-4px);
        }

        .produce-basket span:nth-child(3) {
            background: #60975a;
        }

        /* ======================================
           FRESH FOOD DISPLAY
        ====================================== */

        .fresh-display {
            position: absolute;
            z-index: 3;
            top: 48%;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 10px 16px;
            border-radius: 10px;
            background: rgba(255,255,255,.75);
            box-shadow: 0 8px 20px rgba(0,0,0,.08);
            font-size: 26px;
        }

        .fresh-caption {
            position: absolute;
            z-index: 3;
            top: 44%;
            left: 50%;
            transform: translateX(-50%);
            color: #31543b;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 1.5px;
            white-space: nowrap;
        }

        /* Floor */
        .floor {
            position: absolute;
            z-index: 1;
            bottom: 0;
            left: -5%;
            width: 110%;
            height: 32%;
            background:
                repeating-linear-gradient(
                    90deg,
                    transparent 0,
                    transparent 100px,
                    rgba(53,92,63,.1) 101px,
                    rgba(53,92,63,.1) 103px
                ),
                linear-gradient(180deg, #aabd9f, #718d72);
            clip-path: polygon(7% 0, 93% 0, 100% 100%, 0 100%);
        }

        /* ======================================
           SHOPPERS
        ====================================== */

        .shopper {
            position: absolute;
            z-index: 4;
            bottom: 12%;
            width: 90px;
            height: 210px;
            transform-origin: bottom center;
        }

        .shopper-one {
            left: 25%;
            animation: shopperOne 11s ease-in-out infinite;
        }

        .shopper-two {
            left: 68%;
            bottom: 10%;
            transform: scale(.84);
            animation: shopperTwo 13s ease-in-out infinite;
        }

        .shopper-three {
            left: 48%;
            bottom: 11%;
            transform: scale(.68);
            animation: shopperThree 15s ease-in-out infinite;
        }

        .shopper svg {
            width: 100%;
            height: 100%;
            overflow: visible;
            filter: drop-shadow(0 7px 5px rgba(0,0,0,.18));
        }

        @keyframes shopperOne {
            0%, 100% { translate: 0 0; rotate: -1deg; }
            30% { translate: 25px 0; rotate: 2deg; }
            60% { translate: 52px 0; rotate: -2deg; }
            85% { translate: 18px 0; }
        }

        @keyframes shopperTwo {
            0%, 100% { translate: 0 0; }
            35% { translate: -35px 0; }
            70% { translate: -62px -2px; }
        }

        @keyframes shopperThree {
            0%, 100% { translate: 0 0; }
            50% { translate: 28px 0; }
        }

        .walking-leg {
            transform-box: fill-box;
            transform-origin: top center;
            animation: legWalk .55s ease-in-out infinite alternate;
        }

        .walking-leg.delay {
            animation-delay: -.55s;
        }

        .walking-arm {
            transform-box: fill-box;
            transform-origin: top center;
            animation: armWalk .7s ease-in-out infinite alternate;
        }

        @keyframes legWalk {
            from { transform: rotate(-12deg); }
            to { transform: rotate(12deg); }
        }

        @keyframes armWalk {
            from { transform: rotate(10deg); }
            to { transform: rotate(-10deg); }
        }

        /* ======================================
           SHOPPING CART
        ====================================== */

        .shopping-cart {
            position: absolute;
            z-index: 5;
            left: 39%;
            bottom: 9%;
            width: 180px;
            animation: cartTravel 8s ease-in-out infinite alternate;
            filter: drop-shadow(0 7px 5px rgba(0,0,0,.17));
        }

        @keyframes cartTravel {
            from { translate: -10px 0; }
            to { translate: 38px 0; }
        }

        /* ======================================
           OVERLAY AND BRANDING
        ====================================== */

        .scene-shade {
            position: absolute;
            z-index: 6;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(90deg, rgba(12,42,28,.35), transparent 75%),
                linear-gradient(0deg, rgba(9,36,24,.94), transparent 57%);
        }

        .brand-logo {
            position: absolute;
            z-index: 8;
            top: 42px;
            left: 7%;
            display: flex;
            align-items: center;
            gap: 13px;
            color: white;
            text-decoration: none;
        }

        .brand-icon {
            display: grid;
            place-items: center;
            width: 48px;
            height: 48px;
            border-radius: 13px;
            background: var(--gold);
            color: var(--green);
            font-size: 25px;
            font-weight: 800;
            box-shadow: 0 8px 24px rgba(0,0,0,.16);
        }

        .brand-name {
            font-size: 21px;
            font-weight: 800;
        }

        .brand-subtitle {
            margin-top: 3px;
            color: rgba(255,255,255,.72);
            font-size: 9px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .brand-content {
            position: absolute;
            z-index: 8;
            left: 7%;
            bottom: 15%;
            max-width: 480px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 22px;
            padding: 9px 15px;
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 30px;
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(8px);
            color: white;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }

        .brand-badge span {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--gold);
            box-shadow: 0 0 10px var(--gold);
        }

        .brand-content h1 {
            margin: 0 0 18px;
            color: white;
            font-size: clamp(38px, 4vw, 59px);
            line-height: 1.07;
            letter-spacing: -2px;
            font-weight: 800;
        }

        .brand-content h1 strong {
            color: var(--gold);
        }

        .brand-content p {
            max-width: 410px;
            margin: 0;
            color: rgba(255,255,255,.85);
            font-size: 14px;
            line-height: 1.8;
        }

        .brand-footer {
            position: absolute;
            z-index: 8;
            right: 7%;
            bottom: 33px;
            left: 7%;
            display: flex;
            justify-content: space-between;
            padding-top: 17px;
            border-top: 1px solid rgba(255,255,255,.22);
            color: rgba(255,255,255,.68);
            font-size: 11px;
        }

        .online-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            margin-right: 6px;
            border-radius: 50%;
            background: #55dfa0;
        }

        /* ======================================
           LOGIN FORM
        ====================================== */

        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 55px 35px;
            background:
                radial-gradient(circle at 95% 5%, #e4eee5 0, transparent 35%),
                #f7f9f7;
        }

        .form-card {
            width: 100%;
            max-width: 445px;
            padding: 43px;
            border: 1px solid #e8eee9;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 24px 70px rgba(30,65,43,.08);
        }

        .mobile-logo {
            display: none;
            align-items: center;
            gap: 10px;
            margin-bottom: 34px;
            color: var(--green);
            font-weight: 800;
        }

        .mobile-logo span {
            display: grid;
            place-items: center;
            width: 37px;
            height: 37px;
            border-radius: 10px;
            background: var(--gold);
        }

        .form-heading {
            margin: 0 0 9px;
            color: #17271e;
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .form-description {
            margin: 0 0 30px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .status-message {
            margin-bottom: 20px;
            padding: 13px 15px;
            border: 1px solid #a7f3d0;
            border-radius: 10px;
            background: #ecfdf5;
            color: #065f46;
            font-size: 13px;
        }

        .passkey-area {
            margin-bottom: 22px;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 13px;
            margin: 23px 0;
            color: #9aa79f;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e8eee9;
        }

        .field {
            margin-bottom: 21px;
        }

        .field label {
            display: block;
            margin-bottom: 9px;
            color: #34483c;
            font-size: 12px;
            font-weight: 700;
        }

        .field input {
            width: 100%;
            height: 51px;
            padding: 0 15px;
            border: 1px solid #e2eae4;
            border-radius: 10px;
            outline: none;
            background: #f8faf8;
            color: #17251e;
            font-family: inherit;
            font-size: 14px;
            transition: .2s ease;
        }

        .field input::placeholder {
            color: #a5b1a9;
        }

        .field input:focus {
            border-color: var(--green2);
            background: white;
            box-shadow: 0 0 0 4px rgba(36,90,64,.09);
        }

        .field-error {
            margin-top: 7px;
            color: #dc4c4c;
            font-size: 12px;
        }

        .field-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 9px;
        }

        .field-top label {
            margin: 0;
        }

        .forgot-link {
            color: var(--green2);
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
        }

        .forgot-link:hover,
        .register-link:hover {
            color: #c27a20;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 2px 0 25px;
        }

        .remember-row input {
            width: 16px;
            height: 16px;
            accent-color: var(--green);
        }

        .remember-row label {
            color: #66776c;
            font-size: 12px;
            cursor: pointer;
        }

        .submit-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 53px;
            border: 0;
            border-radius: 10px;
            background: var(--green);
            color: white;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(21,61,45,.18);
            transition: .2s ease;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            background: var(--green2);
            box-shadow: 0 12px 22px rgba(21,61,45,.22);
        }

        .register-text {
            margin-top: 27px;
            color: #849188;
            font-size: 12px;
            text-align: center;
        }

        .register-link {
            margin-left: 4px;
            color: var(--green);
            font-weight: 800;
            text-decoration: none;
        }

        .secure-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin-top: 27px;
            color: #a0aaa3;
            font-size: 10px;
        }

        /* ======================================
           RESPONSIVE
        ====================================== */

        @media (max-width: 1023px) {
            .login-page {
                display: block;
            }

            .visual-panel {
                display: none;
            }

            .form-panel {
                min-height: 100vh;
            }

            .mobile-logo {
                display: flex;
            }
        }

        @media (max-width: 520px) {
            .form-panel {
                padding: 20px;
            }

            .form-card {
                padding: 28px 22px;
                border-radius: 17px;
            }

            .form-heading {
                font-size: 26px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
            }
        }
    </style>
</head>

<body>
<div class="login-page">

    <!-- ======================================
         LEFT PANEL: EES SUPERMARKET
    ======================================= -->

    <section class="visual-panel">

        <div class="store-scene" aria-hidden="true">

            <div class="ceiling"></div>
            <div class="ceiling-light"></div>
            <div class="ceiling-light two"></div>
            <div class="ceiling-light three"></div>
            <div class="back-wall"></div>

            <div class="store-sign">
                EES <span>FRESH MARKET</span>
            </div>

            <!-- LEFT SHELF: GRAINS AND GROCERIES -->
            <div class="shelf shelf-left">
                <div class="shelf-label">GRAINS & GROCERIES</div>

                <div class="shelf-level">
                    <span class="grocery bag rice"></span>
                    <span class="grocery bag flour"></span>
                    <span class="grocery bottle oil"></span>
                    <span class="grocery tin"></span>
                    <span class="grocery box cereal"></span>
                </div>

                <div class="shelf-level">
                    <span class="grocery bag maize"></span>
                    <span class="grocery jar honey"></span>
                    <span class="grocery bottle juice"></span>
                    <span class="grocery tin green"></span>
                    <span class="grocery bag beans"></span>
                </div>

                <div class="shelf-level">
                    <span class="grocery box pasta"></span>
                    <span class="grocery bread"></span>
                    <span class="grocery tin yellow"></span>
                    <span class="grocery jar peanut"></span>
                    <span class="grocery bottle water"></span>
                </div>
            </div>

            <!-- RIGHT SHELF: CANNED FOODS AND PROVISIONS -->
            <div class="shelf shelf-right">
                <div class="shelf-label">FOOD & PROVISIONS</div>

                <div class="shelf-level">
                    <span class="grocery bottle oil"></span>
                    <span class="grocery tin green"></span>
                    <span class="grocery box cereal"></span>
                    <span class="grocery tin"></span>
                    <span class="grocery jar honey"></span>
                </div>

                <div class="shelf-level">
                    <span class="grocery bread"></span>
                    <span class="grocery bottle juice"></span>
                    <span class="grocery bag flour"></span>
                    <span class="grocery tin yellow"></span>
                    <span class="grocery box pasta"></span>
                </div>

                <div class="shelf-level">
                    <span class="grocery bag rice"></span>
                    <span class="grocery tin"></span>
                    <span class="grocery bottle water"></span>
                    <span class="grocery jar tomato"></span>
                    <span class="grocery box green"></span>
                </div>
            </div>

            <!-- FRESH PRODUCE -->
            <div class="fresh-caption">FRESH FRUITS & VEGETABLES</div>
            <div class="fresh-display">
                🍎 🥑 🍊 🍌 🥕
            </div>

            <div class="floor"></div>

            <!-- SHOPPER ONE -->
            <div class="shopper shopper-one">
                <svg viewBox="0 0 100 225" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="50" cy="218" rx="30" ry="5" fill="#203d2c" opacity=".3"/>
                    <circle cx="50" cy="31" r="19" fill="#925d40"/>
                    <path d="M31 30 Q28 7 51 8 Q72 8 70 33 L65 19 Q48 14 35 27Z" fill="#30231e"/>
                    <circle cx="44" cy="32" r="1.8" fill="#211914"/>
                    <circle cx="57" cy="32" r="1.8" fill="#211914"/>
                    <path d="M45 41 Q51 45 57 41" stroke="#573a2c" stroke-width="1.7" fill="none"/>
                    <path d="M32 55 Q50 47 68 55 L75 119 L25 119Z" fill="#e9a23b"/>
                    <path d="M32 60 L18 103 L27 108 L43 76" fill="#925d40"/>
                    <g class="walking-arm">
                        <path d="M67 60 L84 98 L75 103 L58 76" fill="#925d40"/>
                    </g>
                    <g class="walking-leg">
                        <path d="M32 115 L29 185 L44 185 L49 120" fill="#334d75"/>
                        <path d="M28 181 L45 181 L45 194 L24 194Z" fill="#242b28"/>
                    </g>
                    <g class="walking-leg delay">
                        <path d="M51 119 L56 185 L71 185 L68 115" fill="#334d75"/>
                        <path d="M55 181 L73 181 L77 194 L56 194Z" fill="#242b28"/>
                    </g>
                </svg>
            </div>

            <!-- SHOPPER TWO -->
            <div class="shopper shopper-two">
                <svg viewBox="0 0 100 225" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="50" cy="218" rx="30" ry="5" fill="#203d2c" opacity=".3"/>
                    <circle cx="50" cy="31" r="19" fill="#bd805c"/>
                    <path d="M30 31 Q27 6 51 8 Q73 8 71 34 L66 19 Q49 14 34 26Z" fill="#39271e"/>
                    <circle cx="44" cy="32" r="1.8" fill="#211914"/>
                    <circle cx="57" cy="32" r="1.8" fill="#211914"/>
                    <path d="M45 41 Q51 45 57 41" stroke="#75452f" stroke-width="1.7" fill="none"/>
                    <path d="M31 55 Q50 47 69 55 L75 120 L25 120Z" fill="#c76c67"/>
                    <path d="M32 60 L18 104 L27 108 L43 77" fill="#bd805c"/>
                    <path d="M68 60 L83 101 L75 106 L58 77" fill="#bd805c"/>
                    <g class="walking-leg">
                        <path d="M32 116 L29 185 L44 185 L49 120" fill="#405b45"/>
                        <path d="M28 181 L45 181 L45 194 L24 194Z" fill="#242b28"/>
                    </g>
                    <g class="walking-leg delay">
                        <path d="M51 119 L56 185 L71 185 L68 115" fill="#405b45"/>
                        <path d="M55 181 L73 181 L77 194 L56 194Z" fill="#242b28"/>
                    </g>
                </svg>
            </div>

            <!-- SHOPPER THREE -->
            <div class="shopper shopper-three">
                <svg viewBox="0 0 100 225" xmlns="http://www.w3.org/2000/svg">
                    <ellipse cx="50" cy="218" rx="30" ry="5" fill="#203d2c" opacity=".3"/>
                    <circle cx="50" cy="31" r="19" fill="#75482f"/>
                    <path d="M31 31 Q29 8 51 8 Q72 8 70 34 L65 20 Q49 14 35 27Z" fill="#171e19"/>
                    <circle cx="44" cy="32" r="1.8" fill="#211914"/>
                    <circle cx="57" cy="32" r="1.8" fill="#211914"/>
                    <path d="M45 41 Q51 45 57 41" stroke="#573a2c" stroke-width="1.7" fill="none"/>
                    <path d="M32 55 Q50 47 68 55 L75 119 L25 119Z" fill="#538b71"/>
                    <path d="M32 60 L18 103 L27 108 L43 76" fill="#75482f"/>
                    <path d="M68 60 L84 101 L75 106 L58 76" fill="#75482f"/>
                    <g class="walking-leg">
                        <path d="M32 115 L29 185 L44 185 L49 120" fill="#35445e"/>
                        <path d="M28 181 L45 181 L45 194 L24 194Z" fill="#242b28"/>
                    </g>
                    <g class="walking-leg delay">
                        <path d="M51 119 L56 185 L71 185 L68 115" fill="#35445e"/>
                        <path d="M55 181 L73 181 L77 194 L56 194Z" fill="#242b28"/>
                    </g>
                </svg>
            </div>

            <!-- SHOPPING TROLLEY WITH GROCERIES -->
            <svg class="shopping-cart" viewBox="0 0 190 140" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 12 H28 L48 93 H148 L169 36 H39"
                      fill="none" stroke="#244331" stroke-width="7"
                      stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M51 54 H155 M55 73 H149"
                      stroke="#244331" stroke-width="3"/>
                <path d="M69 38 L74 89 M101 38 L103 89 M132 38 L130 89"
                      stroke="#244331" stroke-width="3"/>

                <!-- groceries in the cart -->
                <rect x="65" y="17" width="21" height="21" rx="4" fill="#e9a23b"/>
                <rect x="91" y="13" width="19" height="25" rx="4" fill="#d65d4b"/>
                <rect x="117" y="18" width="20" height="20" rx="4" fill="#5b9662"/>
                <path d="M143 37 L146 19 Q150 15 154 19 L158 37Z" fill="#e3b83c"/>

                <circle cx="68" cy="111" r="10" fill="#263d32"/>
                <circle cx="137" cy="111" r="10" fill="#263d32"/>
            </svg>

        </div>

        <div class="scene-shade"></div>

        <!-- BRAND -->
        <a href="{{ route('home') }}" class="brand-logo">
            <div class="brand-icon">E</div>
            <div>
                <div class="brand-name">EES POS</div>
                <div class="brand-subtitle">Retail Management System</div>
            </div>
        </a>

        <!-- BRAND MESSAGE -->
        <div class="brand-content">
            <div class="brand-badge">
                <span></span>
                Smarter retail starts here
            </div>

            <h1>
                Your business.<br>
                Your store.<br>
                <strong>Your control.</strong>
            </h1>

            <p>
                From groceries and provisions to daily sales and stock
                management, EES POS brings your entire retail business
                together in one simple, powerful workspace.
            </p>
        </div>

        <div class="brand-footer">
            <span>&copy; {{ date('Y') }} EES POS</span>
            <span><i class="online-dot"></i> All systems operational</span>
        </div>

    </section>

    <!-- ======================================
         RIGHT PANEL: LOGIN FORM
    ======================================= -->

    <main class="form-panel">
        <div class="form-card">

            <div class="mobile-logo">
                <span>E</span>
                EES POS
            </div>

            <h2 class="form-heading">Welcome back</h2>

            <p class="form-description">
                Sign in to manage your business and continue where you left off.
            </p>

            @if (session('status'))
                <div class="status-message">
                    {{ session('status') }}
                </div>
            @endif

            <!-- PASSKEY AUTHENTICATION -->
            <div class="passkey-area">
                <x-passkey-verify />
            </div>

            <div class="divider">Or sign in with email</div>

            <!-- EMAIL AND PASSWORD LOGIN -->
            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="field">
                    <label for="email">Email address</label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="you@business.com"
                        required
                        autofocus
                        autocomplete="email"
                    >

                    @error('email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="field">
                    <div class="field-top">
                        <label for="password">Password</label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >

                    @error('password')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="remember-row">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    <label for="remember">Keep me signed in</label>
                </div>

                <button type="submit" class="submit-button">
                    Sign in to your account

                    <svg width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor"
                         stroke-width="2.2" aria-hidden="true">
                        <path d="M5 12h14M13 6l6 6-6 6"
                              stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </button>
            </form>

            <div class="register-text">
                Don't have an account?
                <a href="{{ route('register') }}" class="register-link">
                    Create one now
                </a>
            </div>

            <div class="secure-note">
                <svg width="14" height="14" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="4" y="10" width="16" height="11" rx="2"/>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3"/>
                </svg>
                Secure access to your business workspace
            </div>

        </div>
    </main>

</div>
</body>
</html>
