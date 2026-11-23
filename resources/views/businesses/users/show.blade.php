@extends('layouts.app')

@section('title', 'Staff Details')
@section('page-heading', 'Staff Details')

@push('styles')
<style>
    .staff-details-page {
        max-width: 1100px;
        margin: 0 auto;
        padding-bottom: 40px;
        color: #17251d;
        font-family: Inter, sans-serif;
    }

    .staff-details-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .staff-details-header h2 {
        font-size: 23px;
        font-weight: 800;
        margin: 0 0 5px;
    }

    .staff-details-header p {
        color: #718078;
        font-size: 13px;
        margin: 0;
    }

    .staff-back-btn {
        background: #173b2d;
        color: #fff;
        border-radius: 9px;
        padding: 11px 17px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .staff-back-btn:hover {
        background: #28634a;
        color: #fff;
    }

    .staff-details-card {
        background: #fff;
        border: 1px solid #e4ebe6;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 7px rgba(20, 50, 35, .025);
    }

    .staff-profile {
        padding: 28px;
        display: flex;
        align-items: center;
        gap: 18px;
        border-bottom: 1px solid #edf1ee;
    }

    .staff-avatar {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: #eaf3ed;
        color: #28634a;
        display: grid;
        place-items: center;
        font-size: 20px;
        font-weight: 900;
        flex-shrink: 0;
    }

    .staff-profile h3 {
        font-size: 19px;
        font-weight: 800;
        margin: 0 0 5px;
    }

    .staff-profile p {
        color: #718078;
        font-size: 12px;
        margin: 0;
    }

    .staff-details-section {
        padding: 24px 28px;
    }

    .staff-details-section h5 {
        font-size: 14px;
        font-weight: 800;
        margin: 0 0 20px;
    }

    .staff-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .staff-info-label {
        color: #8a9890;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 7px;
    }

    .staff-info-value {
        color: #263b2e;
        font-size: 13px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .staff-role-badge,
    .staff-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 20px;
        padding: 7px 11px;
        font-size: 11px;
        font-weight: 800;
    }

    .staff-role-badge {
        color: #28634a;
        background: #eaf3ed;
    }

    .staff-status-badge.active {
        color: #15803d;
        background: #dcfce7;
    }

    .staff-status-badge.inactive {
        color: #b45309;
        background: #fff1e8;
    }

    @media (max-width: 600px) {
        .staff-details-header h2 {
            font-size: 20px;
        }

        .staff-back-btn {
            width: 100%;
            justify-content: center;
        }

        .staff-profile,
        .staff-details-section {
            padding: 20px;
        }

        .staff-info-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }
    }
</style>
@endpush

@section('content')
<div class="staff-details-page">

    <div class="staff-details-header">
        <div>
            <h2>Staff Member Details</h2>
            <p>Business membership information for {{ $business->name }}.</p>
        </div>

        <a href="{{ route('business.users.index', ['business' => $business->slug]) }}"
           class="staff-back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Staff Directory
        </a>
    </div>

    <div class="staff-details-card">

        <div class="staff-profile">
            <div class="staff-avatar">
                {{ $staff->initials() }}
            </div>

            <div>
                <h3>{{ $staff->name }}</h3>
                <p>{{ $staff->email }}</p>
            </div>
        </div>

        <div class="staff-details-section">
            <h5>Personal Information</h5>

            <div class="staff-info-grid">
                <div>
                    <div class="staff-info-label">Full Name</div>
                    <div class="staff-info-value">{{ $staff->name }}</div>
                </div>

                <div>
                    <div class="staff-info-label">Email Address</div>
                    <div class="staff-info-value">{{ $staff->email }}</div>
                </div>
            </div>
        </div>

        <div class="staff-details-section" style="border-top: 1px solid #edf1ee;">
            <h5>Business Access</h5>

            <div class="staff-info-grid">
                <div>
                    <div class="staff-info-label">Business</div>
                    <div class="staff-info-value">{{ $business->name }}</div>
                </div>

                <div>
                    <div class="staff-info-label">Assigned Role</div>
                    <div class="staff-info-value">
                        @if ($staff->business_role)
                            <span class="staff-role-badge">
                                {{ $staff->business_role }}
                            </span>
                        @else
                            Not assigned
                        @endif
                    </div>
                </div>

                <div>
                    <div class="staff-info-label">Membership Status</div>
                    <div class="staff-info-value">
                        <span class="staff-status-badge {{ $staff->pivot->status === 'active' ? 'active' : 'inactive' }}">
                            <i class="fas {{ $staff->pivot->status === 'active' ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>
                            {{ ucfirst($staff->pivot->status) }}
                        </span>
                    </div>
                </div>

                <div>
                    <div class="staff-info-label">Date Joined</div>
                    <div class="staff-info-value">
                        {{ $staff->pivot->joined_at
                            ? \Illuminate\Support\Carbon::parse($staff->pivot->joined_at)->format('d M Y')
                            : '—' }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
