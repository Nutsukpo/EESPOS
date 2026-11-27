<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForceNoRemember
{
    public function handle(Request $request, Closure $next): Response
    {
        // Never allow a login request to create a persistent remember-me cookie.
        if ($request->isMethod('post') && $request->is('login')) {
            $request->merge(['remember' => false]);
        }

        $guard = Auth::guard('web');

        // Reject a legacy persistent-login cookie if it authenticates this request.
        // logout() rotates the user's remember token, invalidating that old cookie.
        $guard->user();

        if ($guard->viaRemember()) {
            $guard->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Please sign in again.'], 401);
            }

            return redirect()->guest(route('login'));
        }

        return $next($request);
    }
}
