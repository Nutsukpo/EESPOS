@extends('layouts.app')

@section('title', 'Business Profile')
@section('page-heading', 'Business Profile')

@push('styles')
<style>
    .bp-wrap { width:min(100%,1040px); margin:0 auto; color:#17251d; }
    .bp-head { margin-bottom:22px; }
    .bp-eyebrow { margin:0 0 8px; color:#28634a; font-size:11px; font-weight:800; letter-spacing:1.2px; text-transform:uppercase; }
    .bp-title { margin:0; color:#14291e; font-size:29px; font-weight:800; letter-spacing:-.6px; }
    .bp-subtitle { margin:8px 0 0; color:#64736a; font-size:14px; line-height:1.6; }
    .bp-banner { display:flex; align-items:center; gap:15px; margin-bottom:22px; padding:21px 23px; border-radius:16px; background:linear-gradient(115deg,#102b20,#173b2d 58%,#28634a); color:#fff; box-shadow:0 9px 25px rgba(16,43,32,.13); }
    .bp-icon { display:grid; place-items:center; flex:0 0 48px; width:48px; height:48px; border-radius:13px; background:#e9a23b; color:#102b20; font-size:20px; }
    .bp-banner strong { display:block; margin-bottom:4px; font-size:15px; }
    .bp-banner span { color:rgba(255,255,255,.82); font-size:12px; }
    .bp-card { overflow:hidden; border:1px solid #e3ebe5; border-radius:17px; background:#fff; box-shadow:0 12px 34px rgba(20,45,31,.055); }
    .bp-card-head { display:flex; align-items:center; justify-content:space-between; gap:15px; padding:21px 25px; border-bottom:1px solid #edf1ee; background:#fbfcfb; }
    .bp-card-head h2 { margin:0; color:#17251d; font-size:15px; font-weight:800; }
    .bp-card-head p { margin:4px 0 0; color:#718078; font-size:11.5px; }
    .bp-edit { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:40px; padding:0 16px; border-radius:9px; background:#173b2d; color:#fff; text-decoration:none; font-size:12px; font-weight:800; }
    .bp-edit:hover { background:#28634a; color:#fff; }
    .bp-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:0 28px; padding:10px 25px 20px; }
    .bp-item { padding:19px 0; border-bottom:1px solid #edf1ee; min-width:0; }
    .bp-label { display:block; margin-bottom:7px; color:#7b8980; font-size:10px; font-weight:800; letter-spacing:.8px; text-transform:uppercase; }
    .bp-value { color:#26392e; font-size:13px; font-weight:650; overflow-wrap:anywhere; }
    .bp-empty { color:#9aa69e; font-weight:500; }
    .bp-notice { margin-bottom:18px; padding:12px 15px; border:1px solid #b9dfc5; border-radius:10px; background:#effaf2; color:#216b38; font-size:13px; }
    @media(max-width:640px) { .bp-grid { grid-template-columns:1fr; padding:5px 18px 15px; } .bp-card-head { align-items:flex-start; flex-direction:column; padding:18px; } .bp-edit { width:100%; } .bp-banner { align-items:flex-start; padding:17px; } .bp-title { font-size:25px; } }
</style>
@endpush

@section('content')
<div class="bp-wrap">
    <header class="bp-head">
        <p class="bp-eyebrow"><i class="fas fa-building"></i> EES POS · Workspace settings</p>
        <h1 class="bp-title">Business Profile</h1>
        <p class="bp-subtitle">View your registered business information and keep your details up to date.</p>
    </header>

    <section class="bp-banner">
        <div class="bp-icon"><i class="fas fa-store"></i></div>
        <div>
            <strong>{{ $business->name }}</strong>
            <span>Your business information and contact details</span>
        </div>
    </section>

    @if(session('success'))
        <div class="bp-notice"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
    @endif

    <section class="bp-card">
        <div class="bp-card-head">
            <div>
                <h2>Business information</h2>
                <p>Details saved in your EES POS business profile.</p>
            </div>
            @if($canEdit)
                <a class="bp-edit" href="{{ route('business.profile.edit', ['business' => $business->slug]) }}">
                    <i class="fas fa-pen"></i> Edit Profile
                </a>
            @endif
        </div>

        <div class="bp-grid">
            <div class="bp-item">
                <span class="bp-label">Business Name</span>
                <div class="bp-value">{{ $business->name }}</div>
            </div>
            <div class="bp-item">
                <span class="bp-label">Business Type</span>
                <div class="bp-value">{{ ucfirst($business->business_type) }}</div>
            </div>
            <div class="bp-item">
                <span class="bp-label">Phone Number</span>
                <div class="bp-value {{ $business->phone ? '' : 'bp-empty' }}">{{ $business->phone ?: 'Not provided' }}</div>
            </div>
            <div class="bp-item">
                <span class="bp-label">Business Email</span>
                <div class="bp-value {{ $business->email ? '' : 'bp-empty' }}">{{ $business->email ?: 'Not provided' }}</div>
            </div>
            <div class="bp-item">
                <span class="bp-label">Currency</span>
                <div class="bp-value">{{ $business->currency }}</div>
            </div>
            <div class="bp-item">
                <span class="bp-label">Tax Number</span>
                <div class="bp-value {{ $business->tax_number ? '' : 'bp-empty' }}">{{ $business->tax_number ?: 'Not provided' }}</div>
            </div>
            <div class="bp-item" style="grid-column:1/-1;">
                <span class="bp-label">Business Address</span>
                <div class="bp-value {{ $business->address ? '' : 'bp-empty' }}">{{ $business->address ?: 'Not provided' }}</div>
            </div>
        </div>
    </section>
</div>
@endsection
