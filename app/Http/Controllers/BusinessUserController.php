<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BusinessUserController extends Controller
{
    /**
     * Display the create-user form.
     */
    public function create(Request $request, Business $business)
    {
        abort_unless(
            $request->user()->can('staff.create'),
            403,
            'You are not authorized to create business users.'
        );

        $canAssignRoles = $request->user()->can('staff.assign_roles');

        $roles = $canAssignRoles
            ? \Spatie\Permission\Models\Role::query()
                ->where('business_id', $business->id)
                ->where('guard_name', 'web')
                ->where('name', '!=', 'Owner')
                ->orderBy('name')
                ->get()
            : collect();

        return view('businesses.users.create', compact(
            'business',
            'roles',
            'canAssignRoles'
        ));
    }

    /**
     * Create a staff account and attach it to the current business.
     */
    public function store(Request $request, Business $business)
    {
        abort_unless(
            $request->user()->can('staff.create'),
            403,
            'You are not authorized to create business users.'
        );

        $canAssignRoles = $request->user()->can('staff.assign_roles');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ];

        if ($canAssignRoles) {
            $rules['role'] = [
                'required',
                'string',
                Rule::exists('roles', 'name')->where(
                    fn ($query) => $query
                        ->where('business_id', $business->id)
                        ->where('guard_name', 'web')
                        ->where('name', '!=', 'Owner')
                ),
            ];
        }

        $validated = $request->validate($rules);
        $email = Str::lower(trim($validated['email']));

        $result = DB::transaction(function () use (
            $validated,
            $email,
            $business,
            $canAssignRoles
        ) {
            $existingUser = User::where('email', $email)->first();

            if (
                $existingUser &&
                (int) $existingUser->id === (int) $business->owner_id
            ) {
                throw ValidationException::withMessages([
                    'email' => 'The business owner cannot be added through staff management.',
                ]);
            }

            if (
                $existingUser &&
                $business->users()->whereKey($existingUser->id)->exists()
            ) {
                throw ValidationException::withMessages([
                    'email' => 'This account already belongs to this business.',
                ]);
            }

            $isNewUser = !$existingUser;

            if ($isNewUser) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $email,
                    'password' => Str::random(64),
                ]);
            } else {
                // Preserve existing global account details and password.
                $user = $existingUser;
            }

            $business->users()->attach($user->id, [
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $roleName = $canAssignRoles
                ? $validated['role']
                : 'Cashier';

            $registrar = app(PermissionRegistrar::class);
            $previousTeamId = $registrar->getPermissionsTeamId();

            try {
                $registrar->setPermissionsTeamId($business->id);

                $role = Role::query()
                    ->where('business_id', $business->id)
                    ->where('guard_name', 'web')
                    ->where('name', $roleName)
                    ->where('name', '!=', 'Owner')
                    ->firstOrFail();

                $user->unsetRelation('roles');
                $user->assignRole($role);
            } finally {
                $registrar->setPermissionsTeamId($previousTeamId);
            }

            return [
                'user' => $user,
                'is_new' => $isNewUser,
            ];
        });

        if ($result['is_new']) {
            // Notifications are sent only after the transaction commits.
            $result['user']->sendEmailVerificationNotification();

            Password::sendResetLink([
                'email' => $result['user']->email,
            ]);
        }

        return redirect()
            ->route('business.users.index', [
                'business' => $business->slug,
            ])
            ->with(
                'success',
                $result['is_new']
                    ? 'Staff account created and password setup instructions generated.'
                    : 'Existing account successfully added to this business.'
            );
    }

    /**
     * Display users belonging to the current business.
     */
    public function index(Request $request, Business $business)
    {
        abort_unless(
            $request->user()->can('staff.view'),
            403,
            'You are not authorized to view business users.'
        );

        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => [
                'nullable',
                'string',
                Rule::exists('roles', 'name')
                    ->where('business_id', $business->id)
                    ->where('guard_name', 'web'),
            ],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $search = trim((string) $request->query('search', ''));
        $roleFilter = $request->query('role');
        $statusFilter = $request->query('status');

        // Statistics always represent all business members.
        $totalUsers = $business->users()->count();

        $activeUsers = $business->users()
            ->wherePivot('status', 'active')
            ->count();

        $inactiveUsers = $business->users()
            ->wherePivot('status', '!=', 'active')
            ->count();

        $usersQuery = $business->users()
            ->with('roles');

        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter) {
            $usersQuery->whereHas('roles', function ($query) use ($roleFilter, $business) {
                $query->where('roles.business_id', $business->id)
                    ->where('roles.name', $roleFilter)
                    ->where('roles.guard_name', 'web');
            });
        }

        if ($statusFilter) {
            $usersQuery->wherePivot('status', $statusFilter);
        }

        $users = $usersQuery->orderBy('name')->get();

        foreach ($users as $user) {
            $user->setAttribute(
                'business_role',
                $user->roles->pluck('name')->implode(', ')
            );
        }

        $roles = Role::query()
            ->where('business_id', $business->id)
            ->where('guard_name', 'web')
            ->where('name', '!=', 'Owner')
            ->orderBy('name')
            ->pluck('name');

        return view('businesses.users.index', compact(
            'business',
            'users',
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'roles',
            'search',
            'roleFilter',
            'statusFilter'
        ));
    }

    /**
     * Display a staff member belonging to the current business.
     */
    public function show(Request $request, Business $business, User $user)
    {
        abort_unless(
            $request->user()->can('staff.view'),
            403,
            'You are not authorized to view business users.'
        );

        $staff = $business->users()
            ->whereKey($user->id)
            ->with('roles')
            ->firstOrFail();

        $staff->setAttribute(
            'business_role',
            $staff->roles->pluck('name')->implode(', ')
        );

        return view('businesses.users.show', compact(
            'business',
            'staff'
        ));
    }

    /**
     * Display the business-specific staff edit form.
     */
    public function edit(Request $request, Business $business, User $user)
    {
        abort_unless(
            $request->user()->can('staff.edit'),
            403,
            'You are not authorized to edit business users.'
        );

        abort_if(
            (int) $user->id === (int) $business->owner_id,
            403,
            'The business owner cannot be edited through staff management.'
        );

        $staff = $business->users()
            ->whereKey($user->id)
            ->firstOrFail();

        $canAssignRoles = $request->user()->can('staff.assign_roles');

        $roles = $canAssignRoles
            ? Role::query()
                ->where('business_id', $business->id)
                ->where('guard_name', 'web')
                ->where('name', '!=', 'Owner')
                ->orderBy('name')
                ->get()
            : collect();

        $registrar = app(PermissionRegistrar::class);
        $previousTeamId = $registrar->getPermissionsTeamId();

        try {
            $registrar->setPermissionsTeamId($business->id);
            $assignedRole = $staff->roles()
                ->where('roles.business_id', $business->id)
                ->value('name');
        } finally {
            $registrar->setPermissionsTeamId($previousTeamId);
        }

        return view('businesses.users.edit', compact(
            'business',
            'staff',
            'roles',
            'canAssignRoles',
            'assignedRole'
        ));
    }

    /**
     * Update a user's membership and business-specific role.
     */
    public function update(Request $request, Business $business, User $user)
    {
        abort_unless(
            $request->user()->can('staff.edit'),
            403,
            'You are not authorized to edit business users.'
        );

        abort_if(
            (int) $user->id === (int) $business->owner_id,
            403,
            'The business owner cannot be edited through staff management.'
        );

        $canAssignRoles = $request->user()->can('staff.assign_roles');

        $rules = [
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];

        if ($canAssignRoles) {
            $rules['role'] = ['required', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use (
            $business,
            $user,
            $validated,
            $canAssignRoles
        ) {
            $staff = $business->users()
                ->whereKey($user->id)
                ->firstOrFail();

            $business->users()->updateExistingPivot($staff->id, [
                'status' => $validated['status'],
            ]);

            if ($canAssignRoles) {
                $role = Role::query()
                    ->where('business_id', $business->id)
                    ->where('guard_name', 'web')
                    ->where('name', $validated['role'])
                    ->where('name', '!=', 'Owner')
                    ->first();

                if (! $role) {
                    throw ValidationException::withMessages([
                        'role' => 'Please select a valid role for this business.',
                    ]);
                }

                $registrar = app(PermissionRegistrar::class);
                $previousTeamId = $registrar->getPermissionsTeamId();

                try {
                    $registrar->setPermissionsTeamId($business->id);
                    $staff->unsetRelation('roles');
                    $staff->syncRoles([$role]);
                } finally {
                    $registrar->setPermissionsTeamId($previousTeamId);
                }
            }
        });

        return redirect()
            ->route('business.users.show', [
                'business' => $business->slug,
                'user' => $user->id,
            ])
            ->with('success', 'Staff membership updated successfully.');
    }


    /**
     * Remove a user's membership from the current business only.
     */
    public function destroy(Request $request, Business $business, User $user)
    {
        abort_unless(
            $request->user()->can('staff.delete'),
            403,
            'You are not authorized to remove business users.'
        );

        abort_if(
            (int) $user->id === (int) $business->owner_id,
            403,
            'The business owner cannot be removed.'
        );

        DB::transaction(function () use ($business, $user) {
            $staff = $business->users()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $registrar = app(PermissionRegistrar::class);
            $previousTeamId = $registrar->getPermissionsTeamId();

            try {
                $registrar->setPermissionsTeamId($business->id);

                $isOwner = $staff->roles()
                    ->where('roles.business_id', $business->id)
                    ->where('roles.guard_name', 'web')
                    ->where('roles.name', 'Owner')
                    ->exists();

                if ($isOwner) {
                    $activeOwners = $business->users()
                        ->wherePivot('status', 'active')
                        ->whereHas('roles', function ($query) use ($business) {
                            $query->where('roles.business_id', $business->id)
                                ->where('roles.guard_name', 'web')
                                ->where('roles.name', 'Owner');
                        })
                        ->count();

                    if ($activeOwners <= 1) {
                        throw ValidationException::withMessages([
                            'user' => 'The last active Owner cannot be removed from the business.',
                        ]);
                    }
                }

                // Clear only this business's role assignments.
                $staff->unsetRelation('roles');
                $staff->syncRoles([]);

                // Detach only this business membership. Keep the global account.
                $business->users()->detach($staff->id);
            } finally {
                $registrar->setPermissionsTeamId($previousTeamId);
            }
        });

        return redirect()
            ->route('business.users.index', ['business' => $business->slug])
            ->with('success', 'Staff membership removed from this business successfully.');
    }

}
