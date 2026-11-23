@extends('layouts.app')

@section('title', 'Add Business User')
@section('page-heading', 'Add Business User')

@push('styles')
<style>
    .user-form-page {
        max-width: 1050px;
        margin: 0 auto;
        padding-bottom: 40px;
        color: #17251d;
    }

    .form-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .form-page-header h2 {
        font-size: 23px;
        font-weight: 800;
        margin: 0 0 5px;
    }

    .form-page-header p {
        color: #718078;
        font-size: 13px;
        margin: 0;
    }

    .back-link {
        color: #28634a;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
    }

    .back-link:hover { color: #173b2d; }

    .user-form-card {
        background: #fff;
        border: 1px solid #e4ebe6;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 7px rgba(20,50,35,.025);
        margin-bottom: 20px;
    }

    .form-card-heading {
        padding: 20px 24px;
        border-bottom: 1px solid #edf1ee;
    }

    .form-card-heading h5 {
        font-size: 15px;
        font-weight: 800;
        margin: 0 0 5px;
    }

    .form-card-heading p {
        font-size: 12px;
        color: #718078;
        margin: 0;
    }

    .form-card-body { padding: 24px; }

    .form-label {
        color: #34483b;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 7px;
    }

    .form-control, .form-select {
        min-height: 43px;
        border: 1px solid #dfe8e1;
        border-radius: 8px;
        font-size: 13px;
        color: #263b2e;
    }

    .form-control:focus, .form-select:focus {
        border-color: #28634a;
        box-shadow: 0 0 0 3px rgba(40,99,74,.08);
    }

    .form-text {
        color: #849188;
        font-size: 11px;
    }

    .required-mark { color: #dc2626; }

    .account-notice {
        display: flex;
        gap: 12px;
        padding: 15px;
        background: #f0f7f2;
        border: 1px solid #dcebe0;
        border-radius: 10px;
        color: #345a43;
        font-size: 12px;
        line-height: 1.6;
    }

    .account-notice i {
        font-size: 17px;
        margin-top: 2px;
    }

    .account-notice strong {
        display: block;
        margin-bottom: 3px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 18px 24px;
        border-top: 1px solid #edf1ee;
        background: #fbfcfb;
    }

    .btn-cancel {
        padding: 10px 17px;
        border: 1px solid #dfe8e1;
        border-radius: 8px;
        color: #52645a;
        background: #fff;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
    }

    .btn-save-user {
        padding: 10px 18px;
        border: 0;
        border-radius: 8px;
        color: #fff;
        background: #173b2d;
        font-size: 12px;
        font-weight: 700;
    }

    .btn-save-user:hover {
        background: #28634a;
    }

    @media(max-width: 600px) {
        .form-card-body { padding: 18px; }
        .form-actions { padding: 15px 18px; }
        .form-page-header h2 { font-size: 20px; }
    }
</style>
@endpush

@section('content')
<div class="user-form-page">

    <div class="form-page-header">
        <div>
            <h2>Add New User</h2>
            <p>Add a staff member to {{ $business->name }}.</p>
        </div>

        <a href="{{ route('business.users.index', ['business' => $business->slug]) }}"
           class="back-link">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Users
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('business.users.store', ['business' => $business->slug]) }}">
        @csrf

        <div class="user-form-card">
            <div class="form-card-heading">
                <h5><i class="fas fa-user me-2"></i>Personal Information</h5>
                <p>Enter the staff member's login account details.</p>
            </div>

            <div class="form-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">
                            Full Name <span class="required-mark">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="Enter full name"
                               maxlength="255"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">
                            Email Address <span class="required-mark">*</span>
                        </label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="staff@example.com"
                               maxlength="255"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            If this email already has an EES POS account, that account will be linked.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="user-form-card">
            <div class="form-card-heading">
                <h5><i class="fas fa-shield-halved me-2"></i>Business Access</h5>
                <p>Choose the role this staff member will have in this business.</p>
            </div>

            <div class="form-card-body">
                <div class="row g-3">
                    @if ($canAssignRoles)
                        <div class="col-md-6">
                            <label for="role" class="form-label">
                                Business Role <span class="required-mark">*</span>
                            </label>

                            <select id="role"
                                    name="role"
                                    class="form-select @error('role') is-invalid @enderror"
                                    required>
                                <option value="">Select a role</option>

                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}"
                                        @selected(old('role') === $role->name)>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        <div class="col-md-6">
                            <label class="form-label">Default Business Role</label>
                            <div class="form-control d-flex align-items-center">
                                <span class="badge text-bg-success me-2">Cashier</span>
                                Automatically assigned to new staff
                            </div>
                            <div class="form-text">
                                You do not have permission to assign business roles.
                            </div>
                        </div>
                    @endif
                </div>

                <div class="account-notice mt-4">
                    <i class="fas fa-circle-info"></i>
                    <div>
                        <strong>Account security</strong>
                        New accounts will receive a secure password-setup invitation.
                        Existing EES POS accounts will retain their current login credentials.
                        Their access to this business will be controlled by the assigned business role.
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('business.users.index', ['business' => $business->slug]) }}"
                   class="btn-cancel">
                    Cancel
                </a>

                <button type="submit" class="btn-save-user">
                    <i class="fas fa-user-plus me-1"></i>
                    Create Business User
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
