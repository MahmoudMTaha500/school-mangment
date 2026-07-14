<?php

namespace App\Modules\Wallet\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SIS\Domain\Models\ParentProfile;
use App\Modules\SIS\Domain\Models\Student;
use App\Modules\Wallet\Application\ApplyWalletTransaction;
use App\Modules\Wallet\Application\CreateWalletAccount;
use App\Modules\Wallet\Application\ManageWalletAccount;
use App\Modules\Wallet\Domain\Models\WalletAccount;
use App\Modules\Wallet\Domain\Models\WalletTransaction;
use App\Modules\Wallet\Interfaces\Http\Requests\UpdateWalletAccountRequest;
use App\Modules\Wallet\Interfaces\Http\Resources\WalletAccountResource;
use App\Modules\Wallet\Interfaces\Http\Resources\WalletTransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WalletController extends Controller
{
    public function accounts(Request $request): JsonResponse
    {
        $data = $request->validate(['owner_type' => ['nullable', 'in:parent,student'], 'status' => ['nullable', 'in:active,archived'], 'currency' => ['nullable', 'string', 'size:3'], 'sort' => ['nullable', 'in:balance_cached,currency,created_at'], 'direction' => ['nullable', 'in:asc,desc'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $ownerClass = match ($data['owner_type'] ?? null) {
            'parent' => ParentProfile::class, 'student' => Student::class, default => null
        };

        return WalletAccountResource::collection(WalletAccount::query()->with('owner')->when($ownerClass, fn ($query, string $type) => $query->where('owner_type', $type))->when($data['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))->when($data['currency'] ?? null, fn ($query, string $currency) => $query->where('currency', strtoupper($currency)))->orderBy($data['sort'] ?? 'created_at', $data['direction'] ?? 'desc')->paginate($data['per_page'] ?? 30)->withQueryString())->response();
    }

    public function showAccount(WalletAccount $walletAccount): JsonResponse
    {
        return WalletAccountResource::make($walletAccount->load('owner'))->response();
    }

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

    public function updateAccount(UpdateWalletAccountRequest $request, WalletAccount $walletAccount, ManageWalletAccount $manageWalletAccount): JsonResponse
    {
        return WalletAccountResource::make($manageWalletAccount->update($walletAccount, $request->validated()))->response();
    }

    public function archiveAccount(WalletAccount $walletAccount, ManageWalletAccount $manageWalletAccount): JsonResponse
    {
        $manageWalletAccount->archive($walletAccount);

        return response()->json(status: 204);
    }

    /** @return array{account_id:int,type:string,amount:int,idempotency_key:string,reference_type?:string,reference_id?:int} */
    private function validatedTransaction(Request $request, string $type): array
    {
        return $request->validate(['account_id' => ['required', 'integer', 'exists:wallet_accounts,id'], 'amount' => ['required', 'integer', 'min:1'], 'idempotency_key' => ['required', 'string', 'max:100'], 'reference_type' => ['nullable', 'string', 'max:255'], 'reference_id' => ['nullable', 'integer']]) + ['type' => $type];
    }
}
