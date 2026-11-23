@extends('layouts.app')

@section('title', 'Edit Business Profile')
@section('page-heading', 'Edit Business Profile')

@push('styles')
<style>
    .be-wrap { width:min(100%,1040px); margin:0 auto; color:#17251d; }
    .be-heading { margin-bottom:22px; }
    .be-eyebrow { margin:0 0 8px; color:#28634a; font-size:11px; font-weight:800; letter-spacing:1.2px; text-transform:uppercase; }
    .be-title { margin:0; color:#14291e; font-size:29px; font-weight:800; }
    .be-subtitle { margin:8px 0 0; color:#64736a; font-size:14px; line-height:1.6; }
    .be-card { overflow:hidden; border:1px solid #e3ebe5; border-radius:18px; background:#fff; box-shadow:0 12px 34px rgba(20,45,31,.055); }
    .be-card-head { display:flex; align-items:center; gap:12px; padding:22px 26px; border-bottom:1px solid #edf1ee; background:#fbfcfb; }
    .be-icon { width:40px; height:40px; flex:0 0 40px; display:grid; place-items:center; border-radius:12px; background:#eaf3ed; color:#173b2d; }
    .be-card-head h2 { margin:0; color:#17251d; font-size:15px; font-weight:800; }
    .be-card-head p { margin:4px 0 0; color:#718078; font-size:11.5px; }
    .be-form { padding:26px; }
    .be-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:21px 22px; }
    .be-full { grid-column:1/-1; }
    .be-label { display:block; margin:0 0 8px; color:#26392e; font-size:12px; font-weight:700; }
    .be-required { color:#d4382b; }
    .be-control { display:block; width:100%; min-height:46px; padding:11px 13px; border:1px solid #dce5de; border-radius:10px; outline:none; background:#f9fbf9; color:#17251d; font:inherit; font-size:13px; line-height:1.5; }
    .be-control:focus { border-color:#28634a; background:#fff; box-shadow:0 0 0 3px rgba(40,99,74,.12); }
    .be-control.is-invalid { border-color:#dc3545; background:#fffafa; }
    textarea.be-control { min-height:105px; resize:vertical; }
    .be-error { margin:7px 0 0; color:#c62828; font-size:11.5px; line-height:1.5; }
    .be-footer { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-top:28px; padding-top:21px; border-top:1px solid #edf1ee; }
    .be-actions { display:flex; align-items:center; gap:10px; }
    .be-button { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:45px; padding:0 20px; border:1px solid #173b2d; border-radius:10px; background:linear-gradient(135deg,#173b2d,#28634a); color:#fff; font-size:12.5px; font-weight:800; text-decoration:none; cursor:pointer; }
    .be-button:hover { background:#102b20; color:#fff; }
    .be-cancel { display:inline-flex; align-items:center; justify-content:center; min-height:45px; padding:0 18px; border:1px solid #dce5de; border-radius:10px; color:#526258; text-decoration:none; font-size:12px; font-weight:700; }
    .be-cancel:hover { background:#f4f7f4; color:#173b2d; }
    @media(max-width:640px) { .be-grid { grid-template-columns:1fr; gap:17px; } .be-full { grid-column:auto; } .be-card-head,.be-form { padding:19px; } .be-footer { align-items:stretch; flex-direction:column; } .be-actions { flex-direction:column-reverse; } .be-button,.be-cancel { width:100%; } .be-title { font-size:25px; } }
</style>
@endpush

@section('content')
<div class="be-wrap">
    <header class="be-heading">
        <p class="be-eyebrow"><i class="fas fa-building"></i> EES POS · Workspace settings</p>
        <h1 class="be-title">Edit Business Profile</h1>
        <p class="be-subtitle">Update your business information. Your existing business URL and branch records will remain unchanged.</p>
    </header>

    <section class="be-card">
        <div class="be-card-head">
            <div class="be-icon"><i class="fas fa-clipboard-list"></i></div>
            <div>
                <h2>Business information</h2>
                <p>Fields marked with an asterisk are required.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('business.profile.update', ['business' => $business->slug]) }}" class="be-form">
            @csrf
            @method('PUT')

            <div class="be-grid">
                <div class="be-full">
                    <label for="be-name" class="be-label">Business Name <span class="be-required">*</span></label>
                    <input id="be-name" type="text" name="name" value="{{ old('name', $business->name) }}" required maxlength="255" autocomplete="organization" class="be-control @error('name') is-invalid @enderror">
                    @error('name') <p class="be-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="be-type" class="be-label">Business Type <span class="be-required">*</span></label>
                    <select id="be-type" name="business_type" required class="be-control @error('business_type') is-invalid @enderror">
                        <option value="retail" {{ old('business_type', $business->business_type) == 'retail' ? 'selected' : '' }}>Retail</option>
                        <option value="wholesale" {{ old('business_type', $business->business_type) == 'wholesale' ? 'selected' : '' }}>Wholesale</option>
                        <option value="restaurant" {{ old('business_type', $business->business_type) == 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                        <option value="pharmacy" {{ old('business_type', $business->business_type) == 'pharmacy' ? 'selected' : '' }}>Pharmacy</option>
                        <option value="supermarket" {{ old('business_type', $business->business_type) == 'supermarket' ? 'selected' : '' }}>Supermarket</option>
                        <option value="other" {{ old('business_type', $business->business_type) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('business_type') <p class="be-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="be-phone" class="be-label">Phone Number</label>
                    <input id="be-phone" type="tel" name="phone" value="{{ old('phone', $business->phone) }}" autocomplete="tel" class="be-control @error('phone') is-invalid @enderror">
                    @error('phone') <p class="be-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="be-email" class="be-label">Business Email</label>
                    <input id="be-email" type="email" name="email" value="{{ old('email', $business->email) }}" autocomplete="email" class="be-control @error('email') is-invalid @enderror">
                    @error('email') <p class="be-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="be-currency" class="be-label">Currency <span class="be-required">*</span></label>
                    <select id="be-currency" name="currency" required class="be-control @error('currency') is-invalid @enderror">
                        <option value="GHS" {{ old('currency', $business->currency) == 'GHS' ? 'selected' : '' }}>GHS - Ghana Cedi (₵)</option>
                        <option value="USD" {{ old('currency', $business->currency) == 'USD' ? 'selected' : '' }}>USD - US Dollar ($)</option>
                        <option value="GBP" {{ old('currency', $business->currency) == 'GBP' ? 'selected' : '' }}>GBP - British Pound (£)</option>
                        <option value="EUR" {{ old('currency', $business->currency) == 'EUR' ? 'selected' : '' }}>EUR - Euro (€)</option>
                    </select>
                    @error('currency') <p class="be-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="be-tax" class="be-label">Tax Number <span style="color:#89968e;font-weight:500;">(Optional)</span></label>
                    <input id="be-tax" type="text" name="tax_number" value="{{ old('tax_number', $business->tax_number) }}" class="be-control @error('tax_number') is-invalid @enderror">
                    @error('tax_number') <p class="be-error">{{ $message }}</p> @enderror
                </div>

                <div class="be-full">
                    <label for="be-address" class="be-label">Business Address</label>
                    <textarea id="be-address" name="address" rows="3" autocomplete="street-address" class="be-control @error('address') is-invalid @enderror">{{ old('address', $business->address) }}</textarea>
                    @error('address') <p class="be-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="be-footer">
                <span style="color:#7b8980;font-size:11px;">Your changes will be saved to this business profile.</span>
                <div class="be-actions">
                    <a class="be-cancel" href="{{ route('business.profile.show', ['business' => $business->slug]) }}">Cancel</a>
                    <button type="submit" class="be-button"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection
