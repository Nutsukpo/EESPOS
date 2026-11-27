<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PosSessionController extends Controller
{
    private const IDLE_SECONDS = 900;

    private function sessionKey(Request $request): string
    {
        $business = $request->attributes->get('current_business');
        $user = $request->user();
        abort_unless($business && $user, 403);

        return 'pos_security.' . $business->id . '.' . $user->id;
    }

    public function lock(Request $request): JsonResponse
    {
        $request->session()->put($this->sessionKey($request), [
            'locked' => true,
            'last_activity_at' => now()->timestamp,
        ]);

        return response()->json(['locked' => true]);
    }

    public function activity(Request $request): JsonResponse
    {
        $key = $this->sessionKey($request);
        $state = $request->session()->get($key, []);
        $lastActivity = (int) ($state['last_activity_at'] ?? 0);

        if (($state['locked'] ?? false) || $lastActivity <= 0
            || (now()->timestamp - $lastActivity) >= self::IDLE_SECONDS) {
            $state['locked'] = true;
            $state['last_activity_at'] = $lastActivity ?: now()->timestamp;
            $request->session()->put($key, $state);

            return response()->json([
                'locked' => true,
                'message' => 'The POS is locked. Enter your email and password to continue.',
            ], 423);
        }

        $state['last_activity_at'] = now()->timestamp;
        $request->session()->put($key, $state);

        return response()->json(['active' => true]);
    }

    public function unlock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $emailMatches = Str::lower($validated['email']) === Str::lower((string) $user->email);
        $passwordMatches = Hash::check($validated['password'], $user->password);

        if (!$emailMatches || !$passwordMatches) {
            return response()->json([
                'message' => 'The email or password is incorrect. Please try again.',
            ], 422);
        }

        // Rotate the session ID after successful reauthentication.
        $request->session()->regenerate();
        $request->session()->put($this->sessionKey($request), [
            'locked' => false,
            'last_activity_at' => now()->timestamp,
        ]);

        return response()->json([
            'unlocked' => true,
            'csrf_token' => csrf_token(),
        ]);
    }
}
