<?php

namespace App\Modules\IdentityAccess\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Domain\Models\AuditLog;
use Illuminate\Http\JsonResponse;

final class AuditLogController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => AuditLog::query()->latest('created_at')->paginate(50)]);
    }
}
