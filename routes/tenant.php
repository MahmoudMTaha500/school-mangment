<?php

declare(strict_types=1);

use App\Modules\Attachments\Interfaces\Http\Controllers\AttachmentController;
use App\Modules\Attendance\Interfaces\Http\Controllers\AttendanceController;
use App\Modules\Attendance\Interfaces\Http\Controllers\AttendanceJustificationController;
use App\Modules\Attendance\Interfaces\Http\Controllers\AttendanceReadController;
use App\Modules\Homework\Interfaces\Http\Controllers\HomeworkController;
use App\Modules\Homework\Interfaces\Http\Controllers\HomeworkReadController;
use App\Modules\IdentityAccess\Interfaces\Http\Controllers\AuditLogController;
use App\Modules\IdentityAccess\Interfaces\Http\Controllers\MobileProfileController;
use App\Modules\IdentityAccess\Interfaces\Http\Controllers\TenantAuthController;
use App\Modules\Notifications\Interfaces\Http\Controllers\DeviceTokenController;
use App\Modules\Notifications\Interfaces\Http\Controllers\NotificationController;
use App\Modules\Reporting\Interfaces\Http\Controllers\ReportController;
use App\Modules\SIS\Interfaces\Http\Controllers\MobileStudentController;
use App\Modules\SIS\Interfaces\Http\Controllers\SisController;
use App\Modules\Staff\Interfaces\Http\Controllers\StaffController;
use App\Modules\Wallet\Interfaces\Http\Controllers\StripeWebhookController;
use App\Modules\Wallet\Interfaces\Http\Controllers\TopupController;
use App\Modules\Wallet\Interfaces\Http\Controllers\WalletController;
use App\Modules\Wallet\Interfaces\Http\Controllers\WalletReadController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::prefix('api/v1')->middleware([
    'api',
    'throttle:api',
    'audit',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::post('/auth/login', [TenantAuthController::class, 'login']);
    // Authenticated by Stripe signature, not a bearer token, so it lives
    // outside the auth:sanctum group. Tenancy is already resolved by domain.
    Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [TenantAuthController::class, 'me']);
        Route::get('/me', [MobileProfileController::class, 'me']);
        Route::get('/me/children', [MobileProfileController::class, 'children']);
        Route::post('/auth/logout', [TenantAuthController::class, 'logout']);
        Route::get('/tenant', fn () => response()->json(['data' => ['id' => tenant('id'), 'school' => tenant('name')]]));
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
        Route::put('/notification-preferences', [NotificationController::class, 'updatePreference']);
        Route::get('/notification-preferences', [NotificationController::class, 'preferences']);
        Route::get('/notification-preferences/{preference}', [NotificationController::class, 'showPreference']);
        Route::delete('/notification-preferences/{preference}', [NotificationController::class, 'destroyPreference']);
        Route::post('/me/device-tokens', [DeviceTokenController::class, 'store']);
        Route::delete('/me/device-tokens', [DeviceTokenController::class, 'destroy']);
        Route::middleware('can:school.manage')->get('/audit-logs', [AuditLogController::class, 'index']);
        Route::middleware('can:sis.manage')->group(function (): void {
            Route::get('/sis/students', [SisController::class, 'students']);
            Route::get('/sis/students/{student}', [SisController::class, 'showStudent']);
            Route::post('/sis/academic-years', [SisController::class, 'createAcademicYear']);
            Route::get('/sis/class-sections', [SisController::class, 'classSections']);
            Route::get('/sis/class-sections/{classSection}', [SisController::class, 'showClassSection']);
            Route::post('/sis/class-sections', [SisController::class, 'createClassSection']);
            Route::patch('/sis/class-sections/{classSection}', [SisController::class, 'updateClassSection']);
            Route::delete('/sis/class-sections/{classSection}', [SisController::class, 'archiveClassSection']);
            Route::post('/sis/students', [SisController::class, 'createStudent']);
            Route::patch('/sis/students/{student}', [SisController::class, 'updateStudent']);
            Route::delete('/sis/students/{student}', [SisController::class, 'deleteStudent']);
            Route::post('/sis/students/{student}/account', [SisController::class, 'createStudentAccount']);
            Route::get('/sis/parents', [SisController::class, 'parents']);
            Route::get('/sis/parents/{parent}', [SisController::class, 'showParent']);
            Route::post('/sis/parents', [SisController::class, 'createParent']);
            Route::patch('/sis/parents/{parent}', [SisController::class, 'updateParent']);
            Route::delete('/sis/parents/{parent}', [SisController::class, 'archiveParent']);
            Route::post('/sis/parents/{parent}/students/{student}', [SisController::class, 'linkParent']);
        });
        Route::middleware('can:staff.manage')->group(function (): void {
            Route::get('/staff/teachers', [StaffController::class, 'teachers']);
            Route::get('/staff/teachers/{teacher}', [StaffController::class, 'showTeacher']);
            Route::post('/staff/teachers', [StaffController::class, 'createTeacher']);
            Route::patch('/staff/teachers/{teacher}', [StaffController::class, 'updateTeacher']);
            Route::delete('/staff/teachers/{teacher}', [StaffController::class, 'archiveTeacher']);
            Route::get('/staff/subjects', [StaffController::class, 'subjects']);
            Route::get('/staff/subjects/{subject}', [StaffController::class, 'showSubject']);
            Route::post('/staff/subjects', [StaffController::class, 'createSubject']);
            Route::patch('/staff/subjects/{subject}', [StaffController::class, 'updateSubject']);
            Route::delete('/staff/subjects/{subject}', [StaffController::class, 'deleteSubject']);
            Route::post('/staff/assignments', [StaffController::class, 'assignTeacher']);
        });
        Route::middleware('can:attendance.record')->post('/attendance', [AttendanceController::class, 'store']);
        Route::middleware('can:attendance.record')->patch('/attendance/{attendance}/correct', [AttendanceController::class, 'correct']);
        Route::middleware('can:attendance.record')->patch('/attendance-justifications/{justification}', [AttendanceJustificationController::class, 'review']);
        Route::middleware('can:attendance.view')->get('/attendance', [AttendanceReadController::class, 'index']);
        Route::middleware('can:attendance.view')->post('/attendance/{attendance}/justifications', [AttendanceJustificationController::class, 'store']);
        Route::middleware('can:attendance.view')->get('/me/attendance-summary', [MobileStudentController::class, 'attendanceSummary']);
        Route::middleware('can:homework.create')->post('/homework', [HomeworkController::class, 'store']);
        Route::middleware('can:homework.create')->patch('/homework/{homework}', [HomeworkController::class, 'update']);
        Route::middleware('can:homework.create')->delete('/homework/{homework}', [HomeworkController::class, 'archive']);
        Route::middleware('can:homework.create')->put('/homework/{homework}/rubric', [HomeworkController::class, 'replaceRubric']);
        Route::middleware('can:homework.view')->get('/homework', [HomeworkReadController::class, 'index']);
        Route::middleware('can:homework.view')->get('/homework-options', [HomeworkReadController::class, 'options']);
        Route::middleware('can:homework.view')->get('/homework/{homework}', [HomeworkReadController::class, 'show']);
        Route::middleware('can:homework.grade')->get('/homework/{homework}/submissions', [HomeworkReadController::class, 'submissions']);
        Route::middleware('can:homework.view')->get('/submissions/{submission}', [HomeworkReadController::class, 'showSubmission']);
        Route::middleware('can:homework.view')->get('/me/homework', [MobileStudentController::class, 'homework']);
        Route::middleware('can:homework.submit')->post('/homework/{homework}/submissions', [HomeworkController::class, 'submit']);
        Route::middleware('can:homework.submit')->post('/submissions/{submission}/attachments', [AttachmentController::class, 'storeSubmission']);
        Route::middleware('can:homework.view')->get('/submissions/{submission}/attachments', [AttachmentController::class, 'index']);
        Route::middleware('can:homework.submit')->delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);
        Route::get('/attachments/{attachment}', [AttachmentController::class, 'download']);
        Route::middleware('can:homework.grade')->post('/homework/{homework}/submissions/{submission}/grade', [HomeworkController::class, 'grade']);
        Route::middleware('can:wallet.manage')->group(function (): void {
            Route::get('/wallet/accounts', [WalletController::class, 'accounts']);
            Route::post('/wallet/accounts', [WalletController::class, 'createAccount']);
            Route::get('/wallet/accounts/{walletAccount}', [WalletController::class, 'showAccount']);
            Route::patch('/wallet/accounts/{walletAccount}', [WalletController::class, 'updateAccount']);
            Route::delete('/wallet/accounts/{walletAccount}', [WalletController::class, 'archiveAccount']);
            Route::post('/wallet/credit', [WalletController::class, 'credit']);
            Route::post('/wallet/debit', [WalletController::class, 'debit']);
        });
        Route::middleware('can:wallet.view')->get('/wallet/me', [WalletReadController::class, 'mine']);
        Route::middleware('can:wallet.manage')->get('/wallet/overview', [WalletReadController::class, 'overview']);
        Route::middleware('can:wallet.view')->get('/wallet/me/transactions.csv', [WalletReadController::class, 'transactionsCsv']);
        Route::middleware('can:wallet.topup')->group(function (): void {
            Route::post('/wallet/topups', [TopupController::class, 'create']);
            Route::post('/wallet/topups/{paymentIntent}/confirm', [TopupController::class, 'confirm']);
            Route::post('/wallet/topups/{paymentIntent}/cancel', [TopupController::class, 'cancel']);
        });
        Route::middleware('can:wallet.manage')->post('/wallet/topups/{paymentIntent}/refund', [TopupController::class, 'refund']);
        Route::middleware('can:wallet.manage')->post('/wallet/topups/{paymentIntent}/fail', [TopupController::class, 'fail']);
        Route::middleware('can:wallet.manage')->post('/wallet/topups/reconcile', [TopupController::class, 'reconcile']);
        Route::middleware('can:reports.view')->group(function (): void {
            Route::get('/reports/attendance', [ReportController::class, 'attendance']);
            Route::get('/reports/attendance.csv', [ReportController::class, 'attendanceCsv']);
            Route::get('/reports/wallet', [ReportController::class, 'wallet']);
            Route::get('/reports/homework', [ReportController::class, 'homework']);
        });
    });
});
