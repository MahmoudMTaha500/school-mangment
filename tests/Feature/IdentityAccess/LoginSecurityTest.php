<?php

namespace Tests\Feature\IdentityAccess;

use App\Models\User;
use App\Modules\IdentityAccess\Application\LoginSecurity;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class LoginSecurityTest extends TestCase
{
    private function security(): LoginSecurity
    {
        return app(LoginSecurity::class);
    }

    public function test_password_matches_only_for_a_real_user_with_the_right_password(): void
    {
        $user = new User(['email' => 'a@b.c']);
        $user->password = bcrypt('correct-horse-battery');

        $security = $this->security();

        $this->assertTrue($security->passwordMatches($user, 'correct-horse-battery'));
        $this->assertFalse($security->passwordMatches($user, 'wrong-password'));
    }

    public function test_unknown_user_never_matches_but_still_runs_a_hash_check(): void
    {
        // A null user returns false (no enumeration signal); the decoy hash keeps
        // this path doing the same bcrypt work as the found-user path.
        $this->assertFalse($this->security()->passwordMatches(null, 'anything'));
    }

    public function test_it_throttles_after_five_failures_for_the_same_key(): void
    {
        $security = $this->security();
        $key = $security->throttleKey(request(), 'victim@example.test');

        for ($i = 0; $i < 5; $i++) {
            $security->ensureNotThrottled($key);
            $security->recordFailure($key);
        }

        try {
            $security->ensureNotThrottled($key);
            $this->fail('Expected a throttling ValidationException.');
        } catch (ValidationException $exception) {
            $this->assertSame(429, $exception->status);
            $this->assertArrayHasKey('email', $exception->errors());
        }
    }

    public function test_clearing_resets_the_counter(): void
    {
        $security = $this->security();
        $key = $security->throttleKey(request(), 'ok@example.test');

        for ($i = 0; $i < 4; $i++) {
            $security->recordFailure($key);
        }
        $security->clear($key);

        // Back to a clean slate — must not throw.
        $security->ensureNotThrottled($key);
        $this->assertTrue(true);
    }
}
