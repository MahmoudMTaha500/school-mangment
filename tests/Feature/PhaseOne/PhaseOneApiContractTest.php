<?php

namespace Tests\Feature\PhaseOne;

use App\Modules\Staff\Application\ManageSubject;
use App\Modules\Staff\Domain\Models\Subject;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

final class PhaseOneApiContractTest extends TestCase
{
    public function test_phase_one_resource_routes_are_registered(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())->map(fn ($route) => [implode('|', $route->methods()), $route->uri()]);
        foreach ([
            ['GET|HEAD', 'api/v1/sis/parents/{parent}'], ['PATCH', 'api/v1/sis/parents/{parent}'], ['DELETE', 'api/v1/sis/parents/{parent}'],
            ['GET|HEAD', 'api/v1/staff/teachers/{teacher}'], ['PATCH', 'api/v1/staff/teachers/{teacher}'], ['DELETE', 'api/v1/staff/teachers/{teacher}'],
            ['GET|HEAD', 'api/v1/sis/class-sections/{classSection}'], ['PATCH', 'api/v1/sis/class-sections/{classSection}'], ['DELETE', 'api/v1/sis/class-sections/{classSection}'],
            ['GET|HEAD', 'api/v1/staff/subjects/{subject}'], ['PATCH', 'api/v1/staff/subjects/{subject}'], ['DELETE', 'api/v1/staff/subjects/{subject}'],
            ['GET|HEAD', 'api/v1/homework-options'], ['GET|HEAD', 'api/v1/homework/{homework}'], ['GET|HEAD', 'api/v1/homework/{homework}/submissions'], ['GET|HEAD', 'api/v1/submissions/{submission}'],
            ['GET|HEAD', 'api/v1/wallet/accounts'], ['GET|HEAD', 'api/v1/wallet/accounts/{walletAccount}'], ['PATCH', 'api/v1/wallet/accounts/{walletAccount}'], ['DELETE', 'api/v1/wallet/accounts/{walletAccount}'],
            ['GET|HEAD', 'api/v1/wallet/overview'],
            ['GET|HEAD', 'api/v1/notification-preferences'], ['GET|HEAD', 'api/v1/notification-preferences/{preference}'], ['DELETE', 'api/v1/notification-preferences/{preference}'],
        ] as $expected) {
            $this->assertTrue($routes->contains($expected), "Missing route {$expected[0]} {$expected[1]}");
        }
    }

    public function test_management_routes_require_authentication_and_capabilities(): void
    {
        foreach (Route::getRoutes()->getRoutes() as $route) {
            if (! in_array($route->uri(), ['api/v1/sis/parents/{parent}', 'api/v1/staff/teachers/{teacher}', 'api/v1/wallet/accounts/{walletAccount}'], true)) {
                continue;
            }
            $middleware = $route->gatherMiddleware();
            $this->assertContains('auth:sanctum', $middleware);
            $this->assertTrue(collect($middleware)->contains(fn ($item) => str_starts_with($item, 'can:')));
        }
    }

    public function test_invalid_status_and_pagination_are_rejected(): void
    {
        $validator = Validator::make(['status' => 'deleted', 'per_page' => 1000], ['status' => ['nullable', 'in:active,archived'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
        $this->assertArrayHasKey('per_page', $validator->errors()->toArray());
    }

    public function test_subject_archive_preserves_the_record(): void
    {
        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('status')->default('active');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
        $subject = Subject::query()->create(['name' => 'Physics']);

        app(ManageSubject::class)->archive($subject);

        $this->assertDatabaseCount('subjects', 1);
        $this->assertSame('archived', $subject->refresh()->status);
        $this->assertNotNull($subject->archived_at);
    }
}
