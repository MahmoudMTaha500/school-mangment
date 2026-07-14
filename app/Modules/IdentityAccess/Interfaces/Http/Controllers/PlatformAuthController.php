<?php

namespace App\Modules\IdentityAccess\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\IdentityAccess\Application\LoginSecurity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class PlatformAuthController extends Controller
{
    public function login(Request $request, LoginSecurity $loginSecurity): JsonResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        $throttleKey = $loginSecurity->throttleKey($request, $credentials['email']);
        $loginSecurity->ensureNotThrottled($throttleKey);

        $user = User::query()->where('email', $credentials['email'])->first();

        $passwordMatches = $loginSecurity->passwordMatches($user, $credentials['password']);

        if (! $user || ! $user->is_platform_admin || ! $passwordMatches) {
            $loginSecurity->recordFailure($throttleKey);

            throw ValidationException::withMessages(['email' => ['The supplied credentials are invalid.']]);
        }

        $loginSecurity->clear($throttleKey);

        return response()->json(['token' => $user->createToken('platform-api', ['platform:*'])->plainTextToken]);
    }
}
