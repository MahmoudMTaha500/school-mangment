<?php

namespace App\Modules\Wallet\Interfaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SIS\Domain\Models\ParentProfile;
use App\Modules\SIS\Domain\Models\Student;
use App\Modules\Wallet\Domain\Models\WalletAccount;
use App\Modules\Wallet\Domain\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WalletReadController extends Controller
{
    public function mine(Request $request): JsonResponse
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

        $accounts = WalletAccount::query()->where(function ($query) use ($owners): void {
            foreach ($owners as [$type, $id]) {
                $query->orWhere(fn ($ownerQuery) => $ownerQuery->where('owner_type', $type)->where('owner_id', $id));
            }
        })->get();
        $transactions = WalletTransaction::query()->whereIn('account_id', $accounts->pluck('id'))->latest('created_at')->limit(100)->get();

        return response()->json(['data' => ['accounts' => $accounts, 'transactions' => $transactions]]);
    }
}
