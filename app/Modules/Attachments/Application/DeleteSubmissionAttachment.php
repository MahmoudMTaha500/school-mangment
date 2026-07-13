<?php

namespace App\Modules\Attachments\Application;

use App\Modules\Attachments\Domain\Models\Attachment;
use App\Modules\Homework\Domain\Models\Submission;
use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class DeleteSubmissionAttachment
{
    public function handle(int $userId, Attachment $attachment): void
    {
        abort_unless($attachment->attachable_type === Submission::class, 404);
        $submission = Submission::query()->findOrFail($attachment->attachable_id);
        $student = Student::query()->where('user_id', $userId)->firstOrFail();
        abort_unless($submission->student_id === $student->id, 403, 'You can only delete files from your own submission.');

        $disk = $attachment->disk;
        $path = $attachment->path;
        DB::transaction(fn () => $attachment->delete());
        Storage::disk($disk)->delete($path);
    }
}
