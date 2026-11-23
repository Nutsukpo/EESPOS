<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BusinessController extends Controller
{
    /**
     * Display the business registration form.
     */
    public function create()
    {
        return view('businesses.create');
    }

    /**
     * Store a new business, default roles, owner membership,
     * and the first branch.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'business_type' => [
                'required',
                Rule::in([
                    'retail',
                    'wholesale',
                    'restaurant',
                    'pharmacy',
                    'supermarket',
                    'other',
                ]),
            ],

            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],

            'currency' => [
                'required',
                Rule::in(['GHS', 'USD', 'GBP', 'EUR']),
            ],

            'address' => ['nullable', 'string', 'max:2000'],
            'tax_number' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();

        $business = DB::transaction(function () use (
            $validated,
            $user
        ) {
            /*
             * Generate a unique business slug.
             */
            $baseSlug = Str::slug($validated['name']);

            if ($baseSlug === '') {
                $baseSlug = 'business';
            }

            $slug = $baseSlug;
            $counter = 1;

            while (Business::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            /*
             * Create the business.
             */
            $business = Business::create([
                'owner_id' => $user->id,
                'name' => $validated['name'],
                'slug' => $slug,
                'business_type' => $validated['business_type'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'currency' => $validated['currency'],
                'address' => $validated['address'] ?? null,
                'tax_number' => $validated['tax_number'] ?? null,
                'is_active' => true,
            ]);

            /*
             * Register the owner as an active business member.
             */
            $business->users()->attach($user->id, [
                'status' => 'active',
                'joined_at' => now(),
            ]);

            /*
             * Create the first branch.
             */
            $business->branches()->create([
                'name' => 'Main Branch',
                'code' => 'MAIN',
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'is_main' => true,
                'is_active' => true,
            ]);

            /*
             * Set the current Spatie team to this business.
             */
            $registrar = app(PermissionRegistrar::class);

            $previousTeamId = $registrar->getPermissionsTeamId();

            $registrar->setPermissionsTeamId($business->id);

            try {
                /*
                 * Create default business roles and assign their permissions.
                 * The seeder preserves existing custom permissions.
                 */
                app(\Database\Seeders\BusinessPermissionSeeder::class)->run();

                /*
                 * Assign the Owner role to the business creator.
                 */
                $ownerRole = Role::where('business_id', $business->id)
                    ->where('name', 'Owner')
                    ->where('guard_name', 'web')
                    ->firstOrFail();

                $user->unsetRelation('roles');
                $user->assignRole($ownerRole);

            } finally {
                /*
                 * Restore the previous team context.
                 */
                $registrar->setPermissionsTeamId($previousTeamId);
            }

            return $business;
        });

        /*
         * Redirect to the new business dashboard.
         */
        return redirect()
            ->route('business.dashboard', [
                'business' => $business->slug,
            ])
            ->with(
                'success',
                'Your business has been registered successfully!'
            );
    }
}
