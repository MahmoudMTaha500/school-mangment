<?php

namespace App\Modules\Wallet\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Wallet\Application\ApplyWalletTransaction;
use App\Modules\Wallet\Application\CreateWalletAccount;
use App\Modules\Wallet\Domain\Models\WalletTransaction;
use App\Modules\Wallet\Interfaces\Http\Resources\WalletAccountResource;
use App\Modules\Wallet\Interfaces\Http\Resources\WalletTransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WalletController extends Controller
{
    public function createAccount(Request $request, CreateWalletAccount $createWalletAccount): JsonResponse
    {
        $data = $request->validate(['owner_type' => ['required', 'in:parent,student'], 'owner_id' => ['required', 'integer'], 'currency' => ['nullable', 'string', 'size:3']]);
        $data['currency'] ??= 'USD';

        return WalletAccountResource::make($createWalletAccount->handle($data))->response()->setStatusCode(201);
    }

    public function credit(Request $request, ApplyWalletTransaction $applyWalletTransaction): JsonResponse
    {
        return WalletTransactionResource::make($applyWalletTransaction->handle($this->validatedTransaction($request, WalletTransaction::CREDIT)))->response()->setStatusCode(201);
    }

    public function debit(Request $request, ApplyWalletTransaction $applyWalletTransaction): JsonResponse
    {
        return WalletTransactionResource::make($applyWalletTransaction->handle($this->validatedTransaction($request, WalletTransaction::DEBIT)))->response()->setStatusCode(201);
    }

    /** @return array{account_id:int,type:string,amount:int,idempotency_key:string,reference_type?:string,reference_id?:int} */
    private function validatedTransaction(Request $request, string $type): array
    {
        return $request->validate(['account_id' => ['required', 'integer', 'exists:wallet_accounts,id'], 'amount' => ['required', 'integer', 'min:1'], 'idempotency_key' => ['required', 'string', 'max:100'], 'reference_type' => ['nullable', 'string', 'max:255'], 'reference_id' => ['nullable', 'integer']]) + ['type' => $type];
    }
}
