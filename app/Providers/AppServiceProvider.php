<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;
use Laravel\Passkeys\Passkeys;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Passkeys::ignoreRoutes();

        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        /*
         * Business-scoped Owner authorization.
         *
         * Owners can perform all authorized actions
         * within their currently active business.
         */
        Gate::before(function (User $user, string $ability): ?bool {

            $registrar = app(PermissionRegistrar::class);

            $businessId = $registrar->getPermissionsTeamId();

            // Never grant Owner privileges without a business context.
            if (!$businessId) {
                return null;
            }

            // Grant unrestricted access only in the active business.
            return $user->hasRole('Owner') ? true : null;
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
