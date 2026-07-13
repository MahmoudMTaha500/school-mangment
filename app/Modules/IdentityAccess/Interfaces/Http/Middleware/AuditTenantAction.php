<?php

namespace App\Modules\IdentityAccess\Interfaces\Http\Middleware;

use App\Modules\IdentityAccess\Domain\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuditTenantAction
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if ($response->isSuccessful() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) && ! $request->is('api/v1/auth/*')) {
            AuditLog::query()->create(['user_id' => $request->user()?->id, 'action' => $request->route()?->getActionName() ?? 'unknown', 'method' => $request->method(), 'path' => $request->path(), 'status' => $response->getStatusCode(), 'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 1000), 'context' => $this->safeContext($request)]);
        }

        return $response;
    }

    private function safeContext(Request $request): array
    {
        return $request->except(['password', 'password_confirmation', 'token', 'authorization']);
    }
}
