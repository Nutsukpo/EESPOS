@extends('layouts.app')

@section('title', 'Register Your Business')
@section('page-heading', 'Business Profile')

@push('styles')
<style>
    .business-create {
        --bc-brand: var(--ees-primary, var(--brand, #173b2d));
        --bc-brand-light: var(--ees-primary-light, var(--brand-light, #28634a));
        --bc-accent: var(--ees-accent, var(--brand-accent, #e9a23b));
        --bc-muted: var(--ees-muted, var(--muted, #718078));
        width: min(100%, 1040px);
        margin: 0 auto;
        color: #17251d;
    }
    .business-create *, .business-create *::before, .business-create *::after { box-sizing: border-box; }
    .bc-heading { margin-bottom: 22px; }
    .bc-eyebrow {
        display: inline-flex; align-items: center; gap: 8px; margin: 0 0 9px;
        color: var(--bc-brand-light); font-size: 11px; font-weight: 800;
        letter-spacing: 1.3px; text-transform: uppercase;
    }
    .bc-title {
        margin: 0; color: #14291e; font-size: clamp(24px, 3vw, 31px);
        font-weight: 800; letter-spacing: -.7px; line-height: 1.2;
    }
    .bc-subtitle { max-width: 650px; margin: 9px 0 0; color: #64736a; font-size: 14px; line-height: 1.7; }
    .bc-banner {
        position: relative; overflow: hidden; display: flex; align-items: center; gap: 16px;
        margin-bottom: 22px; padding: 20px 23px; border: 1px solid rgba(233,162,59,.22);
        border-radius: 16px; background: linear-gradient(115deg,#102b20 0%,#173b2d 58%,#28634a 100%);
        box-shadow: 0 9px 25px rgba(16,43,32,.13); color: #fff;
    }
    .bc-banner::after {
        content: ""; position: absolute; width: 180px; height: 180px; right: -50px; top: -100px;
        border: 24px solid rgba(255,255,255,.055); border-radius: 50%; pointer-events: none;
    }
    .bc-banner-icon {
        position: relative; z-index: 1; flex: 0 0 46px; width: 46px; height: 46px;
        display: grid; place-items: center; border-radius: 13px; background: var(--bc-accent);
        color: #102b20; font-size: 19px;
    }
    .bc-banner-copy { position: relative; z-index: 1; }
    .bc-banner-copy strong { display: block; margin-bottom: 4px; color: #fff; font-size: 14px; font-weight: 800; }
    .bc-banner-copy span { display: block; color: rgba(255,255,255,.84); font-size: 12px; line-height: 1.6; }
    .bc-card { overflow: hidden; border: 1px solid #e3ebe5; border-radius: 18px; background: #fff; box-shadow: 0 12px 34px rgba(20,45,31,.055); }
    .bc-card-head {
        display: flex; align-items: center; gap: 12px; padding: 22px 26px;
        border-bottom: 1px solid #edf1ee; background: #fbfcfb;
    }
    .bc-card-head-icon {
        width: 40px; height: 40px; flex: 0 0 40px; display: grid; place-items: center;
        border-radius: 12px; background: #eaf3ed; color: var(--bc-brand); font-size: 16px;
    }
    .bc-card-head h2 { margin: 0; color: #17251d; font-size: 15px; font-weight: 800; }
    .bc-card-head p { margin: 4px 0 0; color: #718078; font-size: 11.5px; }
    .bc-form { padding: 26px; }
    .bc-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 21px 22px; }
    .bc-field-full { grid-column: 1 / -1; }
    .bc-label { display: block; margin: 0 0 8px; color: #26392e; font-size: 12px; font-weight: 700; }
    .bc-required { color: #d4382b; }
    .bc-optional { color: #89968e; font-size: 11px; font-weight: 500; }
    .bc-control {
        display: block; width: 100%; min-height: 46px; padding: 11px 13px;
        border: 1px solid #dce5de; border-radius: 10px; outline: none; background: #f9fbf9;
        color: #17251d; font: inherit; font-size: 13px; line-height: 1.5;
        transition: border-color .18s ease,box-shadow .18s ease,background .18s ease;
    }
    .bc-control::placeholder { color: #9aa69e; }
    .bc-control:hover { border-color: #bdcfc2; }
    .bc-control:focus { border-color: var(--bc-brand-light); background: #fff; box-shadow: 0 0 0 3px rgba(40,99,74,.12); }
    .bc-control.is-invalid { border-color: #dc3545; background: #fffafa; }
    textarea.bc-control { min-height: 105px; resize: vertical; }
    .bc-error { display: flex; align-items: flex-start; gap: 6px; margin: 7px 0 0; color: #c62828; font-size: 11.5px; line-height: 1.5; }
    .bc-error i { margin-top: 2px; }
    .bc-form-footer {
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
        margin-top: 28px; padding-top: 21px; border-top: 1px solid #edf1ee;
    }
    .bc-required-note { margin: 0; color: #7b8980; font-size: 11px; }
    .bc-submit {
        display: inline-flex; align-items: center; justify-content: center; gap: 9px;
        min-height: 46px; padding: 0 22px; border: 1px solid var(--bc-brand);
        border-radius: 10px; background: linear-gradient(135deg,#173b2d,#28634a);
        color: #fff; font-size: 12.5px; font-weight: 800; cursor: pointer;
        box-shadow: 0 7px 16px rgba(23,59,45,.17);
        transition: transform .18s ease,box-shadow .18s ease,background .18s ease;
    }
    .bc-submit:hover { transform: translateY(-1px); background: #102b20; color: #fff; box-shadow: 0 10px 20px rgba(23,59,45,.22); }
    .bc-submit:focus-visible { outline: 3px solid rgba(233,162,59,.55); outline-offset: 3px; }
    @media (max-width: 640px) {
        .bc-grid { grid-template-columns: 1fr; gap: 17px; }
        .bc-field-full { grid-column: auto; }
        .bc-card-head { padding: 18px; }
        .bc-form { padding: 19px; }
        .bc-banner { align-items: flex-start; padding: 17px; }
        .bc-form-footer { align-items: stretch; flex-direction: column; }
        .bc-submit { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="business-create">
    <header class="bc-heading">
        <p class="bc-eyebrow"><i class="fas fa-building"></i> EES POS · Business setup</p>
        <h1 class="bc-title">Register Your Business</h1>
        <p class="bc-subtitle">
            Set up your business profile to personalise your workspace and start managing
            your sales, products and inventory in EES POS.
        </p>
    </header>

    <section class="bc-banner" aria-label="Business setup information">
        <div class="bc-banner-icon" aria-hidden="true"><i class="fas fa-store"></i></div>
        <div class="bc-banner-copy">
            <strong>Let's set up your business</strong>
            <span>Enter your business details below. You can use this information throughout your EES POS workspace.</span>
        </div>
    </section>

    <section class="bc-card">
        <div class="bc-card-head">
            <div class="bc-card-head-icon" aria-hidden="true"><i class="fas fa-clipboard-list"></i></div>
            <div>
                <h2>Business information</h2>
                <p>Fields marked with an asterisk are required.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('business.store') }}" class="bc-form">
            @csrf
            <div class="bc-grid">
                <div class="bc-field-full">
                    <label for="business-name" class="bc-label">Business Name <span class="bc-required">*</span></label>
                    <input id="business-name" type="text" name="name" value="{{ old('name') }}"
                           required maxlength="255" autocomplete="organization"
                           placeholder="e.g. EES Supermarket"
                           class="bc-control @error('name') is-invalid @enderror"
                           aria-invalid="@error('name') true @else false @enderror">
                    @error('name')
                        <p class="bc-error"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></p>
                    @enderror
                </div>

                <div>
                    <label for="business-type" class="bc-label">Business Type <span class="bc-required">*</span></label>
                    <select id="business-type" name="business_type" required
                            class="bc-control @error('business_type') is-invalid @enderror"
                            aria-invalid="@error('business_type') true @else false @enderror">
                        <option value="">Select business type</option>
                        <option value="retail" {{ old('business_type') == 'retail' ? 'selected' : '' }}>Retail</option>
                        <option value="wholesale" {{ old('business_type') == 'wholesale' ? 'selected' : '' }}>Wholesale</option>
                        <option value="restaurant" {{ old('business_type') == 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                        <option value="pharmacy" {{ old('business_type') == 'pharmacy' ? 'selected' : '' }}>Pharmacy</option>
                        <option value="supermarket" {{ old('business_type') == 'supermarket' ? 'selected' : '' }}>Supermarket</option>
                        <option value="other" {{ old('business_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('business_type')
                        <p class="bc-error"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></p>
                    @enderror
                </div>

                <div>
                    <label for="business-phone" class="bc-label">Phone Number</label>
                    <input id="business-phone" type="tel" name="phone" value="{{ old('phone') }}"
                           autocomplete="tel" placeholder="+233 55 123 4567"
                           class="bc-control @error('phone') is-invalid @enderror"
                           aria-invalid="@error('phone') true @else false @enderror">
                    @error('phone')
                        <p class="bc-error"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></p>
                    @enderror
                </div>

                <div>
                    <label for="business-email" class="bc-label">Business Email</label>
                    <input id="business-email" type="email" name="email" value="{{ old('email') }}"
                           autocomplete="email" placeholder="business@example.com"
                           class="bc-control @error('email') is-invalid @enderror"
                           aria-invalid="@error('email') true @else false @enderror">
                    @error('email')
                        <p class="bc-error"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></p>
                    @enderror
                </div>

                <div>
                    <label for="business-currency" class="bc-label">Currency <span class="bc-required">*</span></label>
                    <select id="business-currency" name="currency" required
                            class="bc-control @error('currency') is-invalid @enderror"
                            aria-invalid="@error('currency') true @else false @enderror">
                        <option value="">Select currency</option>
                        <option value="GHS" {{ old('currency', 'GHS') == 'GHS' ? 'selected' : '' }}>GHS - Ghana Cedi (₵)</option>
                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - US Dollar ($)</option>
                        <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP - British Pound (£)</option>
                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro (€)</option>
                    </select>
                    @error('currency')
                        <p class="bc-error"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></p>
                    @enderror
                </div>

                <div class="bc-field-full">
                    <label for="business-address" class="bc-label">Business Address</label>
                    <textarea id="business-address" name="address" rows="3" autocomplete="street-address"
                              placeholder="Enter your business address"
                              class="bc-control @error('address') is-invalid @enderror"
                              aria-invalid="@error('address') true @else false @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="bc-error"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></p>
                    @enderror
                </div>

                <div class="bc-field-full">
                    <label for="business-tax-number" class="bc-label">
                        Tax Number <span class="bc-optional">(Optional)</span>
                    </label>
                    <input id="business-tax-number" type="text" name="tax_number" value="{{ old('tax_number') }}"
                           placeholder="e.g. TIN-123456"
                           class="bc-control @error('tax_number') is-invalid @enderror"
                           aria-invalid="@error('tax_number') true @else false @enderror">
                    @error('tax_number')
                        <p class="bc-error"><i class="fas fa-circle-exclamation"></i><span>{{ $message }}</span></p>
                    @enderror
                </div>
            </div>

            <div class="bc-form-footer">
                <p class="bc-required-note"><span class="bc-required">*</span> Required fields</p>
                <button type="submit" class="bc-submit">
                    <i class="fas fa-check-circle"></i>
                    Register Business
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
