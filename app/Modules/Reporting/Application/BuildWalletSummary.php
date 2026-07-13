<?php

namespace App\Modules\Reporting\Application;

use Illuminate\Support\Facades\DB;

final class BuildWalletSummary
{
    /** @return array{accounts:int,balance:int,credits:int,debits:int} */
    public function handle(string $from, string $to): array
    {
        $totals = DB::table('wallet_transactions')->selectRaw("COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) as credits, COALESCE(SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END), 0) as debits")->whereBetween('created_at', [$from, $to.' 23:59:59'])->first();

        return ['accounts' => (int) DB::table('wallet_accounts')->count(), 'balance' => (int) DB::table('wallet_accounts')->sum('balance_cached'), 'credits' => (int) $totals->credits, 'debits' => (int) $totals->debits];
    }
}
