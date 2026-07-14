<?php

namespace App\Modules\Notifications\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\Domain\Models\InAppNotification;
use App\Modules\Notifications\Domain\Models\NotificationPreference;
use App\Modules\Notifications\Interfaces\Http\Requests\UpdateNotificationPreferenceRequest;
use App\Modules\Notifications\Interfaces\Http\Resources\NotificationPreferenceResource;
use App\Modules\Notifications\Interfaces\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return NotificationResource::collection(InAppNotification::query()->where('notifiable_type', $request->user()::class)->where('notifiable_id', $request->user()->id)->latest()->paginate(30))->response();
    }

    public function markRead(Request $request, InAppNotification $notification): JsonResponse
    {
        abort_unless($notification->notifiable_type === $request->user()::class && $notification->notifiable_id === $request->user()->id, 404);
        $notification->update(['read_at' => now()]);

        return response()->json(status: 204);
    }

    public function updatePreference(UpdateNotificationPreferenceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $preference = NotificationPreference::query()->updateOrCreate(['user_id' => $request->user()->id, 'event_type' => $data['event_type']], ['channels' => array_values(array_unique($data['channels']))]);

        return NotificationPreferenceResource::make($preference)->response();
    }

    public function preferences(Request $request): JsonResponse
    {
        return NotificationPreferenceResource::collection(NotificationPreference::query()->where('user_id', $request->user()->id)->orderBy('event_type')->paginate(100))->response();
    }

    public function showPreference(Request $request, NotificationPreference $preference): JsonResponse
    {
        abort_unless($preference->user_id === $request->user()->id, 404);

        return NotificationPreferenceResource::make($preference)->response();
    }

    public function destroyPreference(Request $request, NotificationPreference $preference): JsonResponse
    {
        abort_unless($preference->user_id === $request->user()->id, 404);
        $preference->delete();

        return response()->json(status: 204);
    }
}
