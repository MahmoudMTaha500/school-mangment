<?php

namespace App\Modules\Notifications\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\Domain\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'in:ios,android,web'],
        ]);

        $token = DeviceToken::query()->updateOrCreate(
            ['token' => $data['token']],
            ['user_id' => $request->user()->id, 'platform' => $data['platform'] ?? null, 'last_used_at' => now()],
        );

        return response()->json(['data' => $token], 201);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:512']]);
        DeviceToken::query()->where('user_id', $request->user()->id)->where('token', $data['token'])->delete();

        return response()->json(status: 204);
    }
}
