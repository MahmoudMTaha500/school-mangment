<?php

namespace App\Modules\Wallet\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Wallet\Application\CancelTopupIntent;
use App\Modules\Wallet\Application\ConfirmTopupIntent;
use App\Modules\Wallet\Application\CreateTopupIntent;
use App\Modules\Wallet\Application\FailTopupIntent;
use App\Modules\Wallet\Application\ReconcileTopupIntents;
use App\Modules\Wallet\Application\RefundTopupIntent;
use App\Modules\Wallet\Application\WalletTopupAccess;
use App\Modules\Wallet\Domain\Models\PaymentIntent;
use App\Modules\Wallet\Domain\Models\WalletAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TopupController extends Controller
{
    public function create(Request $request, CreateTopupIntent $createTopupIntent, WalletTopupAccess $walletTopupAccess): JsonResponse
    {
        $data = $request->validate(['wallet_account_id' => ['required', 'integer', 'exists:wallet_accounts,id'], 'amount' => ['required', 'integer', 'min:1'], 'idempotency_key' => ['required', 'string', 'max:100']]);
        $account = WalletAccount::query()->findOrFail($data['wallet_account_id']);
        $walletTopupAccess->ensureCanTopup($request->user(), $account);
        $result = $createTopupIntent->handle($account, $data['amount'], $data['idempotency_key']);

        return response()->json(['data' => ['intent' => $result['intent'], 'checkout_url' => $result['checkout_url']]], 201);
    }

    public function confirm(Request $request, PaymentIntent $paymentIntent, ConfirmTopupIntent $confirmTopupIntent, WalletTopupAccess $walletTopupAccess): JsonResponse
    {
        $walletTopupAccess->ensureCanTopup($request->user(), $paymentIntent->account);

        return response()->json(['data' => $confirmTopupIntent->handle($paymentIntent)]);
    }

    public function cancel(Request $request, PaymentIntent $paymentIntent, CancelTopupIntent $cancelTopupIntent, WalletTopupAccess $walletTopupAccess): JsonResponse
    {
        $walletTopupAccess->ensureCanTopup($request->user(), $paymentIntent->account);

        return response()->json(['data' => $cancelTopupIntent->handle($paymentIntent)]);
    }

    public function refund(PaymentIntent $paymentIntent, RefundTopupIntent $refundTopupIntent): JsonResponse
    {
        return response()->json(['data' => $refundTopupIntent->handle($paymentIntent)]);
    }

    public function fail(Request $request, PaymentIntent $paymentIntent, FailTopupIntent $failTopupIntent): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        return response()->json(['data' => $failTopupIntent->handle($paymentIntent, $data['reason'])]);
    }

    public function reconcile(Request $request, ReconcileTopupIntents $reconcileTopupIntents): JsonResponse
    {
        $data = $request->validate(['older_than_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'], 'limit' => ['nullable', 'integer', 'min:1', 'max:500']]);

        return response()->json(['data' => $reconcileTopupIntents->handle($data['older_than_minutes'] ?? 30, $data['limit'] ?? 100)]);
    }
}
