<?php

namespace App\Modules\IdentityAccess\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SIS\Domain\Models\ParentProfile;
use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MobileProfileController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(['data' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'roles' => $user->getRoleNames(), 'student' => Student::query()->where('user_id', $user->id)->first(), 'parent' => ParentProfile::query()->where('user_id', $user->id)->first()]]);
    }

    public function children(Request $request): JsonResponse
    {
        $parent = ParentProfile::query()->where('user_id', $request->user()->id)->firstOrFail();

        return response()->json(['data' => $parent->students()->with('classSection')->paginate(30)]);
    }
}
