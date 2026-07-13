<?php

namespace App\Modules\Attachments\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class Attachment extends Model
{
    protected $fillable = ['attachable_type', 'attachable_id', 'uploaded_by', 'disk', 'path', 'original_name', 'mime_type', 'size_bytes'];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
