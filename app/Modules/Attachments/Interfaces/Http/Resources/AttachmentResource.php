<?php

namespace App\Modules\Attachments\Interfaces\Http\Resources;

use App\Modules\Attachments\Domain\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Attachment */
final class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'original_name' => $this->original_name, 'mime_type' => $this->mime_type, 'size_bytes' => $this->size_bytes, 'download_url' => url("/api/v1/attachments/{$this->id}"), 'created_at' => $this->created_at?->toISOString()];
    }
}
