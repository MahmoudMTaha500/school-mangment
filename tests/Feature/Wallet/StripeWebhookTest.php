<?php

namespace Tests\Feature\Wallet;

use App\Modules\Wallet\Application\ConfirmTopupIntent;
use App\Modules\Wallet\Application\FailTopupIntent;
use App\Modules\Wallet\Domain\Models\PaymentIntent;
use App\Modules\Wallet\Domain\Models\WalletAccount;
use App\Modules\Wallet\Interfaces\Http\Controllers\StripeWebhookController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('services.stripe.webhook_secret', self::SECRET);
        config()->set('services.stripe.webhook_tolerance', 300);

        Schema::create('wallet_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->bigInteger('balance_cached')->default(0);
            $table->string('status')->default('active');
            $table->string('currency', 3);
            $table->timestamps();
        });
        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained('wallet_accounts');
            $table->string('type');
            $table->bigInteger('amount');
            $table->bigInteger('balance_after');
            $table->nullableMorphs('reference');
            $table->string('idempotency_key')->unique();
            $table->timestamp('created_at')->useCurrent();
        });
        Schema::create('outbox_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('event_type');
            $table->json('payload');
            $table->timestamp('available_at');
            $table->timestamp('processed_at')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();
        });
        Schema::create('payment_intents', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('wallet_account_id');
            $table->string('gateway');
            $table->string('gateway_payment_id')->unique();
            $table->string('status')->default('pending');
            $table->bigInteger('amount');
            $table->string('currency', 3);
            $table->string('idempotency_key')->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('processed_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('provider');
            $table->string('event_id');
            $table->string('event_type');
            $table->timestamp('processed_at')->useCurrent();
            $table->unique(['provider', 'event_id']);
        });
    }

    public function test_signed_completed_event_credits_the_wallet_once_even_when_replayed(): void
    {
        $account = WalletAccount::query()->create(['owner_type' => 'student', 'owner_id' => 1, 'currency' => 'USD']);
        $intent = PaymentIntent::query()->create([
            'wallet_account_id' => $account->id,
            'gateway' => 'stripe',
            'gateway_payment_id' => 'sandbox_sess_1',
            'amount' => 5000,
            'currency' => 'USD',
            'idempotency_key' => 'topup-1',
            'metadata' => [],
        ]);

        $response = $this->handle($this->event('evt_1', 'checkout.session.completed', 'sandbox_sess_1'));
        $this->assertSame('ok', $response['status']);

        // Replayed delivery of the same event id is dropped.
        $replay = $this->handle($this->event('evt_1', 'checkout.session.completed', 'sandbox_sess_1'));
        $this->assertSame('duplicate', $replay['status']);

        $this->assertSame('succeeded', $intent->refresh()->status);
        $this->assertSame(5000, $account->refresh()->balance_cached);
        $this->assertDatabaseCount('wallet_transactions', 1);
        $this->assertDatabaseCount('processed_webhook_events', 1);
    }

    public function test_invalid_signature_is_rejected_and_changes_nothing(): void
    {
        $this->expectException(HttpException::class);

        try {
            $this->handleRaw('{"id":"evt_x","type":"checkout.session.completed"}', 't=1,v1=deadbeef');
        } finally {
            $this->assertDatabaseCount('processed_webhook_events', 0);
        }
    }

    /** @return array<string, mixed> */
    private function handle(string $payload): array
    {
        return $this->handleRaw($payload, $this->signature($payload));
    }

    /** @return array<string, mixed> */
    private function handleRaw(string $payload, string $signature): array
    {
        $request = Request::create('/api/v1/webhooks/stripe', 'POST', [], [], [], [], $payload);
        $request->headers->set('Stripe-Signature', $signature);

        $response = app(StripeWebhookController::class)->handle(
            $request,
            app(ConfirmTopupIntent::class),
            app(FailTopupIntent::class),
        );

        return json_decode($response->getContent(), true);
    }

    private function event(string $id, string $type, string $sessionId): string
    {
        return json_encode(['id' => $id, 'type' => $type, 'data' => ['object' => ['id' => $sessionId]]], JSON_THROW_ON_ERROR);
    }

    private function signature(string $payload): string
    {
        $timestamp = time();

        return "t={$timestamp},v1=".hash_hmac('sha256', $timestamp.'.'.$payload, self::SECRET);
    }
}
