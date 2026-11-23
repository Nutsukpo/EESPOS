<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class BusinessUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();
        $registrar->setPermissionsTeamId(null);

        foreach ([
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
            'staff.assign_roles',
        ] as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $this->owner = User::factory()->create();

        $this->business = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Test Business',
            'slug' => 'test-business',
            'business_type' => 'retail',
            'currency' => 'GHS',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $registrar->setPermissionsTeamId($this->business->id);

        $permissions = Permission::whereIn('name', [
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
            'staff.assign_roles',
        ])->get();

        foreach ([
            'Owner' => $permissions,
            'Manager' => $permissions->whereIn('name', [
                'staff.view',
                'staff.create',
                'staff.edit',
            ]),
            'Cashier' => collect(),
            'Inventory Officer' => collect(),
        ] as $roleName => $rolePermissions) {
            $role = Role::create([
                'name' => $roleName,
                'guard_name' => 'web',
                'business_id' => $this->business->id,
            ]);

            if ($rolePermissions->isNotEmpty()) {
                $role->givePermissionTo($rolePermissions);
            }
        }

        $this->owner->assignRole(
            Role::where('business_id', $this->business->id)
                ->where('name', 'Owner')
                ->firstOrFail()
        );

        $registrar->setPermissionsTeamId(null);
        $registrar->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        parent::tearDown();
    }

    public function test_owner_can_view_business_users(): void
    {
        $response = $this->actingAs($this->owner)->get(
            route('business.users.index', $this->business)
        );

        $response->assertOk();
        $response->assertViewIs('businesses.users.index');
    }

    public function test_owner_can_open_create_user_form(): void
    {
        $response = $this->actingAs($this->owner)->get(
            route('business.users.create', $this->business)
        );

        $response->assertOk();
        $response->assertViewIs('businesses.users.create');
        $response->assertViewHas('canAssignRoles', true);
    }

    public function test_owner_can_create_staff_with_selected_business_role(): void
    {
        $response = $this->actingAs($this->owner)->post(
            route('business.users.store', $this->business),
            [
                'name' => 'Test Cashier',
                'email' => 'cashier@example.test',
                'role' => 'Inventory Officer',
            ]
        );

        $response->assertRedirect(
            route('business.users.index', $this->business)
        );

        $staff = User::where('email', 'cashier@example.test')->firstOrFail();

        $this->assertDatabaseHas('business_user', [
            'business_id' => $this->business->id,
            'user_id' => $staff->id,
            'status' => 'active',
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->business->id);

        $this->assertTrue($staff->hasRole('Inventory Officer'));

        $registrar->setPermissionsTeamId(null);
    }

    public function test_existing_global_account_is_linked_without_changing_password(): void
    {
        $existingUser = User::factory()->create([
            'name' => 'Existing Account',
            'password' => 'original-password',
        ]);

        $originalPassword = $existingUser->password;

        $response = $this->actingAs($this->owner)->post(
            route('business.users.store', $this->business),
            [
                'name' => 'Different Submitted Name',
                'email' => $existingUser->email,
                'role' => 'Cashier',
            ]
        );

        $response->assertRedirect(
            route('business.users.index', $this->business)
        );

        $this->assertDatabaseHas('business_user', [
            'business_id' => $this->business->id,
            'user_id' => $existingUser->id,
            'status' => 'active',
        ]);

        $this->assertSame(
            $originalPassword,
            $existingUser->fresh()->password
        );

        $this->assertSame(
            'Existing Account',
            $existingUser->fresh()->name
        );
    }

    public function test_duplicate_business_membership_is_rejected(): void
    {
        $existingUser = User::factory()->create();

        $this->business->users()->attach($existingUser->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($this->owner)->from(
            route('business.users.create', $this->business)
        )->post(
            route('business.users.store', $this->business),
            [
                'name' => 'Duplicate Staff',
                'email' => $existingUser->email,
                'role' => 'Cashier',
            ]
        );

        $response->assertRedirect(
            route('business.users.create', $this->business)
        );

        $response->assertSessionHasErrors('email');
    }

    public function test_owner_role_cannot_be_assigned_through_staff_creation(): void
    {
        $response = $this->actingAs($this->owner)->post(
            route('business.users.store', $this->business),
            [
                'name' => 'Attempted Owner',
                'email' => 'attempted-owner@example.test',
                'role' => 'Owner',
            ]
        );

        $response->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'attempted-owner@example.test',
        ]);
    }

    public function test_cashier_cannot_create_business_users(): void
    {
        $cashier = User::factory()->create();

        $this->business->users()->attach($cashier->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->business->id);

        $cashier->assignRole(
            Role::where('business_id', $this->business->id)
                ->where('name', 'Cashier')
                ->firstOrFail()
        );

        $registrar->setPermissionsTeamId(null);

        $response = $this->actingAs($cashier)->post(
            route('business.users.store', $this->business),
            [
                'name' => 'Unauthorized Staff',
                'email' => 'unauthorized@example.test',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'unauthorized@example.test',
        ]);
    }

    public function test_manager_creates_staff_with_cashier_role_automatically(): void
    {
        $manager = User::factory()->create();

        $this->business->users()->attach($manager->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->business->id);

        $manager->assignRole(
            Role::where('business_id', $this->business->id)
                ->where('name', 'Manager')
                ->firstOrFail()
        );

        $registrar->setPermissionsTeamId(null);

        $response = $this->actingAs($manager)->post(
            route('business.users.store', $this->business),
            [
                'name' => 'Manager Created Staff',
                'email' => 'manager-staff@example.test',
            ]
        );

        $response->assertRedirect(
            route('business.users.index', $this->business)
        );

        $staff = User::where('email', 'manager-staff@example.test')
            ->firstOrFail();

        $registrar->setPermissionsTeamId($this->business->id);

        $this->assertTrue($staff->hasRole('Cashier'));
        $this->assertFalse($staff->hasRole('Manager'));
        $this->assertFalse($staff->hasRole('Owner'));

        $registrar->setPermissionsTeamId(null);
    }

    public function test_owner_can_update_staff_role_and_membership_status(): void
    {
        $staff = User::factory()->create();

        $this->business->users()->attach($staff->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($this->owner)->put(
            route('business.users.update', [
                'business' => $this->business->slug,
                'user' => $staff->id,
            ]),
            [
                'role' => 'Inventory Officer',
                'status' => 'inactive',
            ]
        );

        $response->assertRedirect(
            route('business.users.show', [
                'business' => $this->business->slug,
                'user' => $staff->id,
            ])
        );

        $this->assertDatabaseHas('business_user', [
            'business_id' => $this->business->id,
            'user_id' => $staff->id,
            'status' => 'inactive',
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->business->id);

        $this->assertTrue($staff->fresh()->hasRole('Inventory Officer'));
        $this->assertFalse($staff->fresh()->hasRole('Cashier'));

        $registrar->setPermissionsTeamId(null);
    }


    public function test_manager_can_update_status_without_changing_staff_role(): void
    {
        $manager = User::factory()->create();
        $staff = User::factory()->create();

        $this->business->users()->attach($manager->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->business->users()->attach($staff->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->business->id);

        $manager->assignRole(
            Role::where('business_id', $this->business->id)
                ->where('name', 'Manager')
                ->firstOrFail()
        );

        $staff->assignRole(
            Role::where('business_id', $this->business->id)
                ->where('name', 'Cashier')
                ->firstOrFail()
        );

        $registrar->setPermissionsTeamId(null);

        $response = $this->actingAs($manager)->put(
            route('business.users.update', [
                'business' => $this->business->slug,
                'user' => $staff->id,
            ]),
            [
                'status' => 'inactive',
            ]
        );

        $response->assertRedirect(
            route('business.users.show', [
                'business' => $this->business->slug,
                'user' => $staff->id,
            ])
        );

        $this->assertDatabaseHas('business_user', [
            'business_id' => $this->business->id,
            'user_id' => $staff->id,
            'status' => 'inactive',
        ]);

        $registrar->setPermissionsTeamId($this->business->id);

        $this->assertTrue($staff->fresh()->hasRole('Cashier'));

        $registrar->setPermissionsTeamId(null);
    }

    public function test_updating_staff_role_does_not_change_role_in_another_business(): void
    {
        $staff = User::factory()->create();

        $this->business->users()->attach($staff->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $secondBusiness = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Second Test Business',
            'slug' => 'second-test-business',
            'business_type' => 'retail',
            'currency' => 'GHS',
            'is_active' => true,
        ]);

        $secondBusiness->users()->attach($staff->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $registrar = app(PermissionRegistrar::class);

        $registrar->setPermissionsTeamId($secondBusiness->id);

        $secondBusinessRole = Role::create([
            'name' => 'Manager',
            'guard_name' => 'web',
            'business_id' => $secondBusiness->id,
        ]);

        $staff->assignRole($secondBusinessRole);

        $registrar->setPermissionsTeamId(null);

        $response = $this->actingAs($this->owner)->put(
            route('business.users.update', [
                'business' => $this->business->slug,
                'user' => $staff->id,
            ]),
            [
                'role' => 'Inventory Officer',
                'status' => 'active',
            ]
        );

        $response->assertRedirect(
            route('business.users.show', [
                'business' => $this->business->slug,
                'user' => $staff->id,
            ])
        );

        $registrar->setPermissionsTeamId($this->business->id);
        $this->assertTrue($staff->fresh()->hasRole('Inventory Officer'));

        $registrar->setPermissionsTeamId($secondBusiness->id);
        $this->assertTrue($staff->fresh()->hasRole('Manager'));

        $registrar->setPermissionsTeamId(null);
    }


    public function test_business_owner_cannot_be_edited_through_staff_management(): void
    {
        $response = $this->actingAs($this->owner)->put(
            route('business.users.update', [
                'business' => $this->business->slug,
                'user' => $this->owner->id,
            ]),
            [
                'role' => 'Cashier',
                'status' => 'inactive',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('business_user', [
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'status' => 'active',
        ]);
    }


    public function test_owner_can_remove_staff_membership_without_deleting_global_account_or_other_memberships(): void
    {
        $staff = User::factory()->create();

        $this->business->users()->attach($staff->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $secondBusiness = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Second Test Business',
            'slug' => 'second-test-business',
            'business_type' => 'retail',
            'currency' => 'GHS',
            'is_active' => true,
        ]);

        $secondBusiness->users()->attach($staff->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($this->owner)->delete(
            route('business.users.destroy', [
                'business' => $this->business->slug,
                'user' => $staff->id,
            ])
        );

        $response->assertRedirect(
            route('business.users.index', $this->business)
        );

        $this->assertDatabaseMissing('business_user', [
            'business_id' => $this->business->id,
            'user_id' => $staff->id,
        ]);

        $this->assertDatabaseHas('business_user', [
            'business_id' => $secondBusiness->id,
            'user_id' => $staff->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
        ]);
    }


    public function test_last_active_owner_cannot_be_removed(): void
    {
        $response = $this->actingAs($this->owner)->delete(
            route('business.users.destroy', [
                'business' => $this->business->slug,
                'user' => $this->owner->id,
            ])
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('business_user', [
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'status' => 'active',
        ]);
    }


    public function test_manager_cannot_remove_staff_without_delete_permission(): void
    {
        $manager = User::factory()->create();
        $staff = User::factory()->create();

        $this->business->users()->attach($manager->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->business->users()->attach($staff->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($this->business->id);

        $manager->assignRole(
            Role::where('business_id', $this->business->id)
                ->where('name', 'Manager')
                ->firstOrFail()
        );

        $registrar->setPermissionsTeamId(null);

        $response = $this->actingAs($manager)->delete(
            route('business.users.destroy', [
                'business' => $this->business->slug,
                'user' => $staff->id,
            ])
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('business_user', [
            'business_id' => $this->business->id,
            'user_id' => $staff->id,
            'status' => 'active',
        ]);
    }


    public function test_removing_staff_clears_only_the_current_business_role(): void
    {
        $staff = User::factory()->create();

        $this->business->users()->attach($staff->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $secondBusiness = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Second Test Business',
            'slug' => 'second-test-business',
            'business_type' => 'retail',
            'currency' => 'GHS',
            'is_active' => true,
        ]);

        $secondBusiness->users()->attach($staff->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $registrar = app(PermissionRegistrar::class);

        $registrar->setPermissionsTeamId($this->business->id);
        $staff->assignRole(
            Role::where('business_id', $this->business->id)
                ->where('name', 'Cashier')
                ->firstOrFail()
        );

        $registrar->setPermissionsTeamId($secondBusiness->id);
        $secondBusinessRole = Role::create([
            'name' => 'Manager',
            'guard_name' => 'web',
            'business_id' => $secondBusiness->id,
        ]);
        $staff->assignRole($secondBusinessRole);

        $registrar->setPermissionsTeamId(null);

        $response = $this->actingAs($this->owner)->delete(
            route('business.users.destroy', [
                'business' => $this->business->slug,
                'user' => $staff->id,
            ])
        );

        $response->assertRedirect(
            route('business.users.index', $this->business)
        );

        $registrar->setPermissionsTeamId($this->business->id);
        $this->assertFalse($staff->fresh()->hasRole('Cashier'));

        $registrar->setPermissionsTeamId($secondBusiness->id);
        $this->assertTrue($staff->fresh()->hasRole('Manager'));

        $registrar->setPermissionsTeamId(null);
    }

}
