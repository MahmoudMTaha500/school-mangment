<?php

namespace App\Modules\Wallet\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Wallet\Application\ConfirmTopupIntent;
use App\Modules\Wallet\Application\CreateTopupIntent;
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
}
