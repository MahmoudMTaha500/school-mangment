<?php

namespace App\Modules\IdentityAccess\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\IdentityAccess\Application\LoginSecurity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class TenantAuthController extends Controller
{
    public function login(Request $request, LoginSecurity $loginSecurity): JsonResponse
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string'], 'device_name' => ['nullable', 'string', 'max:100']]);

        $throttleKey = $loginSecurity->throttleKey($request, $credentials['email']);
        $loginSecurity->ensureNotThrottled($throttleKey);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $loginSecurity->passwordMatches($user, $credentials['password'])) {
            $loginSecurity->recordFailure($throttleKey);

            throw ValidationException::withMessages(['email' => ['The supplied credentials are invalid.']]);
        }

        $loginSecurity->clear($throttleKey);

        return response()->json(['token' => $user->createToken($credentials['device_name'] ?? 'api-client', $user->getAllPermissions()->pluck('name')->all())->plainTextToken]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(['data' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'roles' => $user->getRoleNames(), 'permissions' => $user->getAllPermissions()->pluck('name')]]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(status: 204);
    }
}
