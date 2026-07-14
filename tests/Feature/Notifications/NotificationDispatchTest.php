<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Modules\Notifications\Application\NotificationDispatcher;
use App\Modules\Notifications\Domain\Models\InAppNotification;
use App\Modules\Notifications\Domain\Models\NotificationPreference;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class NotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('event_type');
            $table->json('channels');
            $table->timestamps();
        });
    }

    public function test_container_wired_dispatcher_defaults_to_in_app_only(): void
    {
        $user = User::query()->create(['name' => 'Parent', 'email' => 'parent@example.test', 'password' => 'secret-password']);

        app(NotificationDispatcher::class)->dispatch($user->id, 'WalletCredited', ['message' => 'Wallet credited.']);

        $this->assertDatabaseCount('notifications', 1);
        $notification = InAppNotification::query()->firstOrFail();
        $this->assertSame($user->id, (int) $notification->notifiable_id);
        $this->assertSame('WalletCredited', $notification->type);
    }

    public function test_a_disabled_in_app_preference_suppresses_the_notification(): void
    {
        $user = User::query()->create(['name' => 'Parent', 'email' => 'parent@example.test', 'password' => 'secret-password']);
        NotificationPreference::query()->create(['user_id' => $user->id, 'event_type' => 'WalletCredited', 'channels' => ['email']]);

        app(NotificationDispatcher::class)->dispatch($user->id, 'WalletCredited', ['message' => 'Wallet credited.']);

        $this->assertDatabaseCount('notifications', 0);
    }
}
