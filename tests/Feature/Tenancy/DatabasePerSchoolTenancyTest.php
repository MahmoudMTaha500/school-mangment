<?php

namespace Tests\Feature\Tenancy;

use App\Modules\Tenancy\Infrastructure\Persistence\SchoolTenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

final class DatabasePerSchoolTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_tenant_is_stored_centrally_with_its_domain(): void
    {
        Event::fake();

        $school = SchoolTenant::create(['id' => 'green-valley', 'name' => 'Green Valley School']);
        $school->domains()->create(['domain' => 'green-valley.localhost']);

        $this->assertSame('Green Valley School', SchoolTenant::findOrFail('green-valley')->name);
        $this->assertSame('green-valley.localhost', $school->domains()->sole()->domain);
        $this->assertSame(SchoolTenant::class, config('tenancy.tenant_model'));
    }
}
