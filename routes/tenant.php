<?php

declare(strict_types=1);

use App\Modules\Attachments\Interfaces\Http\Controllers\AttachmentController;
use App\Modules\Attendance\Interfaces\Http\Controllers\AttendanceController;
use App\Modules\Attendance\Interfaces\Http\Controllers\AttendanceReadController;
use App\Modules\Homework\Interfaces\Http\Controllers\HomeworkController;
use App\Modules\Homework\Interfaces\Http\Controllers\HomeworkReadController;
use App\Modules\IdentityAccess\Interfaces\Http\Controllers\AuditLogController;
use App\Modules\IdentityAccess\Interfaces\Http\Controllers\MobileProfileController;
use App\Modules\IdentityAccess\Interfaces\Http\Controllers\TenantAuthController;
use App\Modules\Notifications\Interfaces\Http\Controllers\NotificationController;
use App\Modules\Reporting\Interfaces\Http\Controllers\ReportController;
use App\Modules\SIS\Interfaces\Http\Controllers\MobileStudentController;
use App\Modules\SIS\Interfaces\Http\Controllers\SisController;
use App\Modules\Staff\Interfaces\Http\Controllers\StaffController;
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
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [TenantAuthController::class, 'me']);
        Route::get('/me', [MobileProfileController::class, 'me']);
        Route::get('/me/children', [MobileProfileController::class, 'children']);
        Route::post('/auth/logout', [TenantAuthController::class, 'logout']);
        Route::get('/tenant', fn () => response()->json(['data' => ['id' => tenant('id'), 'school' => tenant('name')]]));
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
        Route::put('/notification-preferences', [NotificationController::class, 'updatePreference']);
        Route::middleware('can:school.manage')->get('/audit-logs', [AuditLogController::class, 'index']);
        Route::middleware('can:sis.manage')->group(function (): void {
            Route::get('/sis/students', [SisController::class, 'students']);
            Route::post('/sis/academic-years', [SisController::class, 'createAcademicYear']);
            Route::get('/sis/class-sections', [SisController::class, 'classSections']);
            Route::post('/sis/class-sections', [SisController::class, 'createClassSection']);
            Route::post('/sis/students', [SisController::class, 'createStudent']);
            Route::patch('/sis/students/{student}', [SisController::class, 'updateStudent']);
            Route::delete('/sis/students/{student}', [SisController::class, 'deleteStudent']);
            Route::post('/sis/students/{student}/account', [SisController::class, 'createStudentAccount']);
            Route::get('/sis/parents', [SisController::class, 'parents']);
            Route::post('/sis/parents', [SisController::class, 'createParent']);
            Route::post('/sis/parents/{parent}/students/{student}', [SisController::class, 'linkParent']);
        });
        Route::middleware('can:staff.manage')->group(function (): void {
            Route::get('/staff/teachers', [StaffController::class, 'teachers']);
            Route::post('/staff/teachers', [StaffController::class, 'createTeacher']);
            Route::get('/staff/subjects', [StaffController::class, 'subjects']);
            Route::post('/staff/subjects', [StaffController::class, 'createSubject']);
            Route::delete('/staff/subjects/{subject}', [StaffController::class, 'deleteSubject']);
            Route::post('/staff/assignments', [StaffController::class, 'assignTeacher']);
        });
        Route::middleware('can:attendance.record')->post('/attendance', [AttendanceController::class, 'store']);
        Route::middleware('can:attendance.record')->patch('/attendance/{attendance}/correct', [AttendanceController::class, 'correct']);
        Route::middleware('can:attendance.view')->get('/attendance', [AttendanceReadController::class, 'index']);
        Route::middleware('can:attendance.view')->get('/me/attendance-summary', [MobileStudentController::class, 'attendanceSummary']);
        Route::middleware('can:homework.create')->post('/homework', [HomeworkController::class, 'store']);
        Route::middleware('can:homework.view')->get('/homework', [HomeworkReadController::class, 'index']);
        Route::middleware('can:homework.view')->get('/me/homework', [MobileStudentController::class, 'homework']);
        Route::middleware('can:homework.submit')->post('/homework/{homework}/submissions', [HomeworkController::class, 'submit']);
        Route::middleware('can:homework.submit')->post('/submissions/{submission}/attachments', [AttachmentController::class, 'storeSubmission']);
        Route::get('/attachments/{attachment}', [AttachmentController::class, 'download']);
        Route::middleware('can:homework.grade')->post('/homework/{homework}/submissions/{submission}/grade', [HomeworkController::class, 'grade']);
        Route::middleware('can:wallet.manage')->group(function (): void {
            Route::post('/wallet/accounts', [WalletController::class, 'createAccount']);
            Route::post('/wallet/credit', [WalletController::class, 'credit']);
            Route::post('/wallet/debit', [WalletController::class, 'debit']);
        });
        Route::middleware('can:wallet.view')->get('/wallet/me', [WalletReadController::class, 'mine']);
        Route::middleware('can:wallet.topup')->group(function (): void {
            Route::post('/wallet/topups', [TopupController::class, 'create']);
            Route::post('/wallet/topups/{paymentIntent}/confirm', [TopupController::class, 'confirm']);
        });
        Route::middleware('can:reports.view')->group(function (): void {
            Route::get('/reports/attendance', [ReportController::class, 'attendance']);
            Route::get('/reports/attendance.csv', [ReportController::class, 'attendanceCsv']);
            Route::get('/reports/wallet', [ReportController::class, 'wallet']);
            Route::get('/reports/homework', [ReportController::class, 'homework']);
        });
    });
});
