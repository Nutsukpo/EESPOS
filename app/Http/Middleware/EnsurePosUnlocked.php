<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePosUnlocked
{
    private const IDLE_SECONDS = 900;

    public function handle(Request $request, Closure $next): Response
    {
        $business = $request->attributes->get('current_business');
        $user = $request->user();
        abort_unless($business && $user, 403);

        $key = 'pos_security.' . $business->id . '.' . $user->id;
        $state = $request->session()->get($key, []);
        $lastActivity = (int) ($state['last_activity_at'] ?? 0);
        $expired = $lastActivity <= 0 || (now()->timestamp - $lastActivity) >= self::IDLE_SECONDS;

        if (($state['locked'] ?? false) || $expired) {
            $state['locked'] = true;
            $state['last_activity_at'] = $lastActivity ?: now()->timestamp;
            $request->session()->put($key, $state);

            return response()->json([
                'locked' => true,
                'message' => 'The POS has been locked after inactivity. Enter your email and password to continue.',
            ], 423);
        }

        return $next($request);
    }
}
