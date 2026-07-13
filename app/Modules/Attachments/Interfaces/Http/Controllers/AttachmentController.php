<?php

namespace App\Modules\Attachments\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Attachments\Application\DeleteSubmissionAttachment;
use App\Modules\Attachments\Application\StoreSubmissionAttachment;
use App\Modules\Attachments\Domain\Models\Attachment;
use App\Modules\Attachments\Interfaces\Http\Resources\AttachmentResource;
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

        return AttachmentResource::make($storeSubmissionAttachment->handle($request->user()->id, $submission, $data['file']))->response()->setStatusCode(201);
    }

    public function index(Request $request, Submission $submission, StudentReadAccess $studentReadAccess): JsonResponse
    {
        $studentReadAccess->ensureCanViewStudent($request->user(), Student::query()->findOrFail($submission->student_id));

        return AttachmentResource::collection(Attachment::query()->where('attachable_type', Submission::class)->where('attachable_id', $submission->id)->latest()->get())->response();
    }

    public function destroy(Request $request, Attachment $attachment, DeleteSubmissionAttachment $deleteSubmissionAttachment): JsonResponse
    {
        $deleteSubmissionAttachment->handle($request->user()->id, $attachment);

        return response()->json(status: 204);
    }

    public function download(Request $request, Attachment $attachment, StudentReadAccess $studentReadAccess): StreamedResponse
    {
        abort_unless($attachment->attachable_type === Submission::class, 404);
        $submission = Submission::query()->findOrFail($attachment->attachable_id);
        $studentReadAccess->ensureCanViewStudent($request->user(), Student::query()->findOrFail($submission->student_id));

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }
}
