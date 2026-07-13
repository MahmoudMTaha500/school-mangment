<?php

namespace Tests\Feature\IdentityAccess;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class PlatformAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_obtain_a_platform_token(): void
    {
        User::query()->create(['name' => 'Platform Admin', 'email' => 'admin@example.test', 'password' => Hash::make('secure-password'), 'is_platform_admin' => true]);

        $this->postJson('/api/v1/platform/login', ['email' => 'admin@example.test', 'password' => 'secure-password'])
            ->assertOk()
            ->assertJsonStructure(['token']);
    }

    public function test_non_platform_user_cannot_obtain_a_platform_token(): void
    {
        User::query()->create(['name' => 'School User', 'email' => 'user@example.test', 'password' => Hash::make('secure-password')]);

        $this->postJson('/api/v1/platform/login', ['email' => 'user@example.test', 'password' => 'secure-password'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_non_platform_user_cannot_provision_a_school(): void
    {
        $user = User::query()->create(['name' => 'School User', 'email' => 'user@example.test', 'password' => Hash::make('secure-password')]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/schools', [])->assertForbidden();
    }
}
