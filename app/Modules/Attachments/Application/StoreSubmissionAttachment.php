<?php

namespace App\Modules\Attachments\Application;

use App\Modules\Attachments\Domain\Models\Attachment;
use App\Modules\Homework\Domain\Models\Submission;
use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Http\UploadedFile;

final class StoreSubmissionAttachment
{
    public function handle(int $userId, Submission $submission, UploadedFile $file): Attachment
    {
        $student = Student::query()->where('user_id', $userId)->firstOrFail();
        abort_unless($submission->student_id === $student->id, 403, 'You can only attach files to your own submission.');
        $path = $file->store("attachments/submissions/{$submission->id}", 'local');

        return Attachment::query()->create(['attachable_type' => Submission::class, 'attachable_id' => $submission->id, 'uploaded_by' => $userId, 'disk' => 'local', 'path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType() ?: 'application/octet-stream', 'size_bytes' => $file->getSize() ?: 0]);
    }
}
