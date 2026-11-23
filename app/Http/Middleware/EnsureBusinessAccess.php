<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Business;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\PermissionRegistrar;

class EnsureBusinessAccess
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $business = $request->route('business');

        // Resolve the business slug if route model binding has not run yet.
        if (is_string($business)) {
            $business = Business::where('slug', $business)->firstOrFail();
            $request->route()->setParameter('business', $business);
        }

        $user = $request->user();

        if (!$business || !$user) {
            abort(403);
        }

        // Check business ownership.
        $isOwner = (int) $business->owner_id === (int) $user->id;

        // Check active business membership.
        $isMember = $user->businesses()
            ->where('businesses.id', $business->id)
            ->wherePivot('status', 'active')
            ->exists();

        if (!$isOwner && !$isMember) {
            abort(403, 'Unauthorized business access.');
        }

        $registrar = app(PermissionRegistrar::class);

        $previousTeamId = $registrar->getPermissionsTeamId();

        // Activate the selected business.
        $registrar->setPermissionsTeamId($business->id);

        // Clear previously loaded authorization relationships.
        $user->unsetRelation('roles');
        $user->unsetRelation('permissions');

        // Store the active business on the request.
        $request->attributes->set(
            'current_business',
            $business
        );

        // Capture business-specific staff visibility while team context is active.
        $request->attributes->set(
            'can_view_business_users',
            $user->can('staff.view')
        );

        try {
            return $next($request);
        } finally {
            // Restore the previous team context.
            $registrar->setPermissionsTeamId($previousTeamId);
        }
    }
}
