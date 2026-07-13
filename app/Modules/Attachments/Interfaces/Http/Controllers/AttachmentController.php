<?php

namespace App\Modules\Attachments\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attachments\Application\StoreSubmissionAttachment;
use App\Modules\Attachments\Domain\Models\Attachment;
use App\Modules\Homework\Domain\Models\Submission;
use App\Modules\SIS\Application\StudentReadAccess;
use App\Modules\SIS\Domain\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AttachmentController extends Controller
{
    public function storeSubmission(Request $request, Submission $submission, StoreSubmissionAttachment $storeSubmissionAttachment): JsonResponse
    {
        $data = $request->validate(['file' => ['required', 'file', 'max:10240', 'mimetypes:application/pdf,image/jpeg,image/png,image/webp,text/plain,application/vnd.openxmlformats-officedocument.wordprocessingml.document']]);

        return response()->json(['data' => $storeSubmissionAttachment->handle($request->user()->id, $submission, $data['file'])], 201);
    }

    public function download(Request $request, Attachment $attachment, StudentReadAccess $studentReadAccess): StreamedResponse
    {
        abort_unless($attachment->attachable_type === Submission::class, 404);
        $submission = Submission::query()->findOrFail($attachment->attachable_id);
        $studentReadAccess->ensureCanViewStudent($request->user(), Student::query()->findOrFail($submission->student_id));

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }
}
