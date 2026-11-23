@extends('layouts.app')

@section('content')
<div class="user-form-page">
    <div class="form-page-header">
        <div>
            <h1>Edit Staff Member</h1>
            <p>Update this staff member's access to {{ $business->name }}.</p>
        </div>

        <a href="{{ route('business.users.show', ['business' => $business->slug, 'user' => $staff->id]) }}"
           class="user-action-btn"
           title="Back to staff details">
            <i class="fas fa-arrow-left"></i>
            Back
        </a>
    </div>

    <div class="user-form-card">
        <form method="POST"
              action="{{ route('business.users.update', ['business' => $business->slug, 'user' => $staff->id]) }}">
            @csrf
            @method('PUT')

            <div class="form-section">
                <h2>Personal Information</h2>
                <p class="section-description">
                    These details belong to the global login account and cannot be changed here.
                </p>

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" value="{{ $staff->name }}" disabled>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" value="{{ $staff->email }}" disabled>
                </div>
            </div>

            <div class="form-section">
                <h2>Business Access</h2>
                <p class="section-description">
                    Manage this person's access to {{ $business->name }}.
                </p>

                @if($canAssignRoles)
                    <div class="form-group">
                        <label for="role">Business Role</label>
                        <select name="role" id="role" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}"
                                    {{ old('role', $assignedRole) === $role->name ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                @else
                    <div class="form-group">
                        <label>Business Role</label>
                        <input type="text" value="{{ $assignedRole ?? 'Not assigned' }}" disabled>
                        <small>You do not have permission to change staff roles.</small>
                    </div>
                @endif

                <div class="form-group">
                    <label for="status">Membership Status</label>
                    <select name="status" id="status" required>
                        <option value="active"
                            {{ old('status', $staff->pivot->status) === 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive"
                            {{ old('status', $staff->pivot->status) === 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                    @error('status')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            @if($errors->any())
                <div class="form-error-message">
                    Please correct the errors above and try again.
                </div>
            @endif

            <div class="form-actions">
                <a href="{{ route('business.users.index', ['business' => $business->slug]) }}"
                   class="btn btn-secondary">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
