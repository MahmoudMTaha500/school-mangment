<?php

namespace Tests\Feature\Notifications;

use App\Modules\Notifications\Application\NotificationDispatcher;
use App\Modules\Notifications\Application\ProcessOutbox;
use App\Modules\Notifications\Domain\Models\OutboxMessage;
use App\Modules\SIS\Domain\Models\Student;
use App\Modules\Wallet\Domain\Models\WalletAccount;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

final class OutboxDeadLetterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('wallet_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->bigInteger('balance_cached')->default(0);
            $table->string('status')->default('active');
            $table->string('currency', 3);
            $table->timestamps();
        });
        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
        Schema::create('outbox_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('event_type');
            $table->json('payload');
            $table->timestamp('available_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();
        });
    }

    public function test_a_failing_delivery_backs_off_then_dead_letters_after_max_attempts(): void
    {
        $student = Student::query()->create(['user_id' => 1]);
        $account = WalletAccount::query()->create(['owner_type' => Student::class, 'owner_id' => $student->id, 'currency' => 'USD']);

        $messageId = DB::table('outbox_messages')->insertGetId([
            'event_type' => 'WalletCredited',
            'payload' => json_encode(['account_id' => $account->id, 'amount' => 100]),
            'available_at' => now(),
            'attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $processOutbox = new ProcessOutbox($this->failingDispatcher());

        // First run: delivery throws, message is retried with backoff, not dead.
        $processOutbox->handle();
        $message = OutboxMessage::query()->findOrFail($messageId);
        $this->assertSame(1, $message->attempts);
        $this->assertNull($message->failed_at);
        $this->assertNull($message->processed_at);
        $this->assertTrue($message->available_at->isFuture());
        $this->assertNotNull($message->last_error);

        // Drive it to the attempt ceiling; it must dead-letter, never deliver.
        for ($i = 0; $i < 5; $i++) {
            OutboxMessage::query()->whereKey($messageId)->update(['available_at' => now()->subMinute()]);
            $processOutbox->handle();
        }

        $message = OutboxMessage::query()->findOrFail($messageId);
        $this->assertSame(5, $message->attempts);
        $this->assertNotNull($message->failed_at);
        $this->assertNull($message->processed_at);
    }

    private function failingDispatcher(): NotificationDispatcher
    {
        return new class extends NotificationDispatcher
        {
            public function __construct()
            {
                parent::__construct([]);
            }

            public function dispatch(int $userId, string $eventType, array $data): void
            {
                throw new RuntimeException('channel unavailable');
            }
        };
    }
}
