<?php

namespace App\Modules\IdentityAccess\Application;

use App\Models\User;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class LoginSecurity
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function __construct(private readonly RateLimiter $limiter) {}

    public function throttleKey(Request $request, string $email): string
    {
        return 'login:'.Str::lower($email).'|'.$request->ip();
    }

    public function ensureNotThrottled(string $key): void
    {
        if ($this->limiter->tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = $this->limiter->availableIn($key);

            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ])->status(429);
        }
    }

    public function recordFailure(string $key): void
    {
        $this->limiter->hit($key, self::DECAY_SECONDS);
    }

    public function clear(string $key): void
    {
        $this->limiter->clear($key);
    }

    public function passwordMatches(?User $user, string $password): bool
    {
        $hash = $user?->getAuthPassword() ?: $this->decoyHash();

        return Hash::check($password, $hash) && $user !== null;
    }

    private function decoyHash(): string
    {
        return Hash::make('login-timing-decoy-'.self::DECAY_SECONDS);
    }
}
