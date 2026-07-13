<?php

namespace App\Modules\IdentityAccess\Domain\Models;

use Illuminate\Database\Eloquent\Model;

final class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'action', 'method', 'path', 'status', 'ip_address', 'user_agent', 'context'];

    protected function casts(): array
    {
        return ['context' => 'array'];
    }
}
