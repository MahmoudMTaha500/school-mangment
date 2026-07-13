<?php

namespace Tests\Feature\Wallet;

use App\Modules\Wallet\Application\ApplyWalletTransaction;
use App\Modules\Wallet\Domain\Models\WalletAccount;
use App\Modules\Wallet\Domain\Models\WalletTransaction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class WalletLedgerTest extends TestCase
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
            $table->string('currency', 3);
            $table->timestamps();
            $table->unique(['owner_type', 'owner_id']);
        });
        Schema::create('wallet_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('account_id')->constrained('wallet_accounts')->cascadeOnDelete();
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
    }

    public function test_credit_is_idempotent_and_creates_one_outbox_message(): void
    {
        $account = WalletAccount::query()->create(['owner_type' => 'student', 'owner_id' => 1, 'currency' => 'USD']);
        $ledger = app(ApplyWalletTransaction::class);
        $first = $ledger->handle(['account_id' => $account->id, 'type' => WalletTransaction::CREDIT, 'amount' => 1500, 'idempotency_key' => 'gateway-event-1']);
        $second = $ledger->handle(['account_id' => $account->id, 'type' => WalletTransaction::CREDIT, 'amount' => 1500, 'idempotency_key' => 'gateway-event-1']);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1500, $account->refresh()->balance_cached);
        $this->assertDatabaseCount('wallet_transactions', 1);
        $this->assertDatabaseCount('outbox_messages', 1);
    }

    public function test_debit_cannot_make_a_balance_negative(): void
    {
        $account = WalletAccount::query()->create(['owner_type' => 'student', 'owner_id' => 1, 'currency' => 'USD']);
        $this->expectException(HttpException::class);
        app(ApplyWalletTransaction::class)->handle(['account_id' => $account->id, 'type' => WalletTransaction::DEBIT, 'amount' => 1, 'idempotency_key' => 'debit-1']);
    }
}
