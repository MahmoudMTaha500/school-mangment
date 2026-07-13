<?php

namespace App\Modules\Wallet\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SIS\Domain\Models\ParentProfile;
use App\Modules\SIS\Domain\Models\Student;
use App\Modules\Wallet\Domain\Models\WalletAccount;
use App\Modules\Wallet\Domain\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class WalletReadController extends Controller
{
    public function mine(Request $request): JsonResponse
    {
        $accounts = $this->accountsFor($request);
        $transactions = WalletTransaction::query()->whereIn('account_id', $accounts->pluck('id'))->latest('created_at')->limit(100)->get();

        return response()->json(['data' => ['accounts' => $accounts, 'transactions' => $transactions]]);
    }

    public function transactionsCsv(Request $request): StreamedResponse
    {
        $accountIds = $this->accountsFor($request)->pluck('id');

        return response()->streamDownload(function () use ($accountIds): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['id', 'account_id', 'type', 'amount_minor', 'balance_after_minor', 'reference_type', 'reference_id', 'created_at']);
            WalletTransaction::query()->whereIn('account_id', $accountIds)->orderBy('created_at')->chunkById(500, function ($transactions) use ($stream): void {
                foreach ($transactions as $transaction) {
                    fputcsv($stream, [$transaction->id, $transaction->account_id, $transaction->type, $transaction->amount, $transaction->balance_after, $transaction->reference_type, $transaction->reference_id, $transaction->created_at?->toISOString()]);
                }
            });
            fclose($stream);
        }, 'wallet-transactions.csv', ['Content-Type' => 'text/csv']);
    }

    /** @return Collection<int, WalletAccount> */
    private function accountsFor(Request $request): Collection
    {
        $user = $request->user();
        $owners = [];
        if ($parent = ParentProfile::query()->where('user_id', $user->id)->first()) {
            $owners[] = [ParentProfile::class, $parent->id];
            foreach ($parent->students()->get(['students.id']) as $student) {
                $owners[] = [Student::class, $student->id];
            }
        }
        if ($student = Student::query()->where('user_id', $user->id)->first()) {
            $owners[] = [Student::class, $student->id];
        }
        abort_if($owners === [], 403, 'No wallet owner profile is associated with this user.');

        return WalletAccount::query()->where(function ($query) use ($owners): void {
            foreach ($owners as [$type, $id]) {
                $query->orWhere(fn ($ownerQuery) => $ownerQuery->where('owner_type', $type)->where('owner_id', $id));
            }
        })->get();
    }
}
