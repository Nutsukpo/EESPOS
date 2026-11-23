@extends('layouts.app')

@section('title', 'Business Users')
@section('page-heading', 'Users Management')

@push('styles')
<style>
    .users-page {
        max-width: 1400px;
        margin: 0 auto;
        padding-bottom: 40px;
        color: #17251d;
        font-family: Inter, sans-serif;
    }

    .users-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .users-header h2 {
        font-size: 23px;
        font-weight: 800;
        margin: 0 0 5px;
    }

    .users-header p {
        color: #718078;
        font-size: 13px;
        margin: 0;
    }

    .btn-add-user {
        background: #173b2d;
        color: #fff;
        border: 0;
        border-radius: 9px;
        padding: 11px 17px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: .2s ease;
    }

    .btn-add-user:hover {
        background: #28634a;
        color: #fff;
        transform: translateY(-1px);
    }

    .users-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 22px;
    }

    .user-stat-card {
        background: #fff;
        border: 1px solid #e4ebe6;
        border-radius: 13px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 2px 7px rgba(20, 50, 35, .025);
    }

    .user-stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .user-stat-icon.total {
        background: #eaf3ed;
        color: #28634a;
    }

    .user-stat-icon.active {
        background: #dcfce7;
        color: #15803d;
    }

    .user-stat-icon.inactive {
        background: #fff1e8;
        color: #c26a32;
    }

    .user-stat-label {
        color: #718078;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 5px;
    }

    .user-stat-value {
        color: #17251d;
        font-size: 25px;
        font-weight: 900;
        line-height: 1;
    }

    .users-panel {
        background: #fff;
        border: 1px solid #e4ebe6;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 7px rgba(20, 50, 35, .025);
    }

    .users-panel-header {
        padding: 20px 22px;
        border-bottom: 1px solid #edf1ee;
    }

    .users-panel-header h5 {
        font-size: 15px;
        font-weight: 800;
        margin: 0 0 4px;
    }

    .users-panel-header p {
        font-size: 12px;
        color: #718078;
        margin: 0;
    }

    .users-filters {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        padding: 17px 22px;
        border-bottom: 1px solid #edf1ee;
    }

    .users-search {
        flex: 1 1 250px;
        position: relative;
    }

    .users-search i {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #91a096;
        font-size: 13px;
    }

    .users-search input {
        width: 100%;
        height: 41px;
        border: 1px solid #e1e9e3;
        border-radius: 8px;
        padding: 0 12px 0 37px;
        outline: none;
        font-size: 12px;
    }

    .users-search input:focus,
    .users-filters select:focus {
        border-color: #28634a;
        box-shadow: 0 0 0 3px rgba(40, 99, 74, .08);
    }

    .users-filters select {
        min-width: 150px;
        height: 41px;
        border: 1px solid #e1e9e3;
        border-radius: 8px;
        padding: 0 12px;
        color: #52645a;
        background: #fff;
        font-size: 12px;
        outline: none;
    }

    .users-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .users-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
    }

    .users-table th {
        background: #f8faf8;
        color: #718078;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .5px;
        text-transform: uppercase;
        padding: 14px 18px;
        white-space: nowrap;
        border-bottom: 1px solid #edf1ee;
    }

    .users-table td {
        padding: 15px 18px;
        border-bottom: 1px solid #f0f3f1;
        vertical-align: middle;
        font-size: 12px;
    }

    .users-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .user-identity {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        background: #eaf3ed;
        color: #28634a;
        display: grid;
        place-items: center;
        font-size: 12px;
        font-weight: 900;
        flex-shrink: 0;
    }

    .user-name {
        color: #17251d;
        font-weight: 800;
        font-size: 12px;
        margin-bottom: 3px;
    }

    .user-email {
        color: #8a9890;
        font-size: 11px;
    }

    .role-badge,
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 20px;
        padding: 6px 10px;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .role-badge {
        color: #28634a;
        background: #eaf3ed;
    }

    .status-badge.active {
        color: #15803d;
        background: #dcfce7;
    }

    .status-badge.inactive {
        color: #b45309;
        background: #fff1e8;
    }

    .user-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .user-action-btn {
        width: 31px;
        height: 31px;
        display: grid;
        place-items: center;
        border: 1px solid #e5ebe6;
        border-radius: 8px;
        color: #52645a;
        background: #fff;
        text-decoration: none;
        font-size: 11px;
    }

    .user-action-btn:hover {
        background: #eaf3ed;
        color: #173b2d;
    }

    .user-action-btn-danger {
        color: #b42318;
        border-color: #f3d0cc;
    }

    .user-action-btn-danger:hover {
        background: #fff0ee;
        color: #b42318;
    }

    .users-empty {
        padding: 55px 20px;
        text-align: center;
        color: #718078;
    }

    .users-empty i {
        font-size: 32px;
        color: #b5c4ba;
        margin-bottom: 12px;
    }

    .users-empty h5 {
        color: #263b2e;
        font-size: 15px;
        font-weight: 800;
    }

    .users-empty p {
        font-size: 12px;
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .users-stats {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .users-header h2 {
            font-size: 20px;
        }

        .users-header .btn-add-user {
            width: 100%;
            justify-content: center;
        }

        .users-panel-header,
        .users-filters {
            padding-left: 15px;
            padding-right: 15px;
        }

        .users-filters select {
            flex: 1 1 130px;
        }
    }
</style>
@endpush

@section('content')
<div class="users-page">

    {{-- PAGE HEADER --}}
    <div class="users-header">
        <div>
            <h2>Business Users</h2>
            <p>Manage staff accounts and access to {{ $business->name }}.</p>
        </div>

        <a href="{{ route('business.users.create', ['business' => $business->slug]) }}"
           class="btn-add-user">
            <i class="fas fa-user-plus"></i>
            Add New User
        </a>
    </div>

    {{-- USER STATISTICS --}}
    <div class="users-stats">
        <div class="user-stat-card">
            <div class="user-stat-icon total">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div class="user-stat-label">Total Users</div>
                <div class="user-stat-value">{{ $totalUsers ?? 0 }}</div>
            </div>
        </div>

        <div class="user-stat-card">
            <div class="user-stat-icon active">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <div class="user-stat-label">Active Users</div>
                <div class="user-stat-value">{{ $activeUsers ?? 0 }}</div>
            </div>
        </div>

        <div class="user-stat-card">
            <div class="user-stat-icon inactive">
                <i class="fas fa-user-clock"></i>
            </div>
            <div>
                <div class="user-stat-label">Inactive Users</div>
                <div class="user-stat-value">{{ $inactiveUsers ?? 0 }}</div>
            </div>
        </div>
    </div>

    {{-- USERS TABLE --}}
    <div class="users-panel">
        <div class="users-panel-header">
            <h5>Staff Directory</h5>
            <p>Business members and their assigned access roles.</p>
        </div>

        <form method="GET"
              action="{{ route('business.users.index', ['business' => $business->slug]) }}"
              class="users-filters">

            <div class="users-search">
                <i class="fas fa-search"></i>
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search by name or email..."
                    aria-label="Search staff by name or email">
            </div>

            <select name="role" aria-label="Filter by business role">
                <option value="">All Roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected($roleFilter === $role)>
                        {{ $role }}
                    </option>
                @endforeach
            </select>

            <select name="status" aria-label="Filter by membership status">
                <option value="">All Statuses</option>
                <option value="active" @selected($statusFilter === 'active')>Active</option>
                <option value="inactive" @selected($statusFilter === 'inactive')>Inactive</option>
            </select>

            <button type="submit" class="btn-add-user">
                <i class="fas fa-filter"></i> Apply
            </button>

            @if ($search !== '' || $roleFilter || $statusFilter)
                <a href="{{ route('business.users.index', ['business' => $business->slug]) }}"
                   class="user-action-btn"
                   title="Clear filters"
                   aria-label="Clear filters">
                    <i class="fas fa-times"></i>
                </a>
            @endif
        </form>

        <div class="users-table-wrap">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Staff Member</th>
                        <th>Business Role</th>
                        <th>Membership Status</th>
                        <th>Date Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users ?? [] as $user)
                        <tr>
                            <td>
                                <div class="user-identity">
                                    <div class="user-avatar">
                                        {{ $user->initials() }}
                                    </div>
                                    <div>
                                        <div class="user-name">{{ $user->name }}</div>
                                        <div class="user-email">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if ($user->business_role)
                                    <span class="role-badge">
                                        {{ $user->business_role }}
                                    </span>
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </td>

                            <td>
                                <span class="status-badge {{ $user->pivot->status === 'active' ? 'active' : 'inactive' }}">
                                    <i class="fas {{ $user->pivot->status === 'active' ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>
                                    {{ ucfirst($user->pivot->status) }}
                                </span>
                            </td>

                            <td class="text-muted">
                                {{ $user->pivot->joined_at ? \Illuminate\Support\Carbon::parse($user->pivot->joined_at)->format('d M Y') : '—' }}
                            </td>

                            <td>
                                <div class="user-actions">
                                    <a href="{{ route('business.users.show', ['business' => $business->slug, 'user' => $user->id]) }}"
                                       class="user-action-btn"
                                       title="View user">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('business.users.edit', ['business' => $business->slug, 'user' => $user->id]) }}"
                                       class="user-action-btn"
                                       title="Edit user">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @if (auth()->user()->can('staff.delete') && (int) $user->id !== (int) $business->owner_id)
                                        <form method="POST"
                                              action="{{ route('business.users.destroy', ['business' => $business->slug, 'user' => $user->id]) }}"
                                              onsubmit="return confirm('Remove {{ addslashes($user->name) }} from this business? Their global login account and other business memberships will remain intact.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="user-action-btn user-action-btn-danger"
                                                    title="Remove staff member"
                                                    aria-label="Remove {{ $user->name }}">
                                                <i class="fas fa-user-minus"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="users-empty">
                                    <i class="fas fa-users"></i>
                                    <h5>No business users found</h5>
                                    <p>Staff members added to this business will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
