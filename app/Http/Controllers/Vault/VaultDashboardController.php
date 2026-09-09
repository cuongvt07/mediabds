<?php

namespace App\Http\Controllers\Vault;

use App\Models\Vault\VaultLedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VaultDashboardController extends VaultBaseController
{
    public function summary(Request $request)
    {
        $user = $request->user('vault');
        $vaults = $user->vaults()->where('status', 'active')->get();

        $totalPrincipal = $vaults->sum('principal_amount');
        $totalInterest = $vaults->sum('accrued_interest_amount');
        $totalBalance = $totalPrincipal + $totalInterest;
        $estimatedDailyInterest = $vaults->sum(fn ($v) => $v->estimatedDailyInterest());

        // % tăng trưởng tháng này = lãi cộng trong 30 ngày qua / tổng gốc.
        $interestLast30Days = VaultLedgerEntry::where('vault_user_id', $user->id)
            ->where('type', 'interest')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('amount');
        $monthlyGrowthPct = $totalPrincipal > 0 ? round(($interestLast30Days / $totalPrincipal) * 100, 1) : 0;

        return $this->ok([
            'totalBalance' => $totalBalance,
            'totalPrincipal' => $totalPrincipal,
            'totalInterestReceived' => $totalInterest,
            'monthlyGrowthPct' => $monthlyGrowthPct,
            'estimatedDailyInterest' => $estimatedDailyInterest,
            'vaultsCount' => $vaults->count(),
        ]);
    }

    public function vaults(Request $request)
    {
        $vaults = $request->user('vault')->vaults()->where('status', 'active')->orderByDesc('id')->get();

        return $this->ok($vaults->map(fn ($v) => $this->transformVault($v))->values());
    }

    /** Biểu đồ "Tăng trưởng lãi 7 ngày qua". */
    public function interestChart(Request $request)
    {
        $user = $request->user('vault');
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->toDateString());

        $entries = VaultLedgerEntry::where('vault_user_id', $user->id)
            ->where('type', 'interest')
            ->where('created_at', '>=', now()->subDays(7)->startOfDay())
            ->get()
            ->groupBy(fn ($e) => Carbon::parse($e->created_at)->toDateString());

        $series = $days->map(function ($date) use ($entries) {
            return [
                'date' => $date,
                'amount' => (int) ($entries->get($date)?->sum('amount') ?? 0),
            ];
        })->values();

        return $this->ok([
            'series' => $series,
            'total' => $series->sum('amount'),
            'average' => $series->count() ? (int) round($series->sum('amount') / $series->count()) : 0,
        ]);
    }

    public function recentActivity(Request $request)
    {
        $entries = VaultLedgerEntry::where('vault_user_id', $request->user('vault')->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return $this->ok($entries->map(fn ($e) => [
            'id' => $e->id,
            'type' => $e->type,
            'amount' => $e->amount,
            'createdAt' => $e->created_at->toIso8601String(),
            'meta' => $e->meta,
        ])->values());
    }

    private function transformVault($v): array
    {
        return [
            'id' => $v->id,
            'type' => $v->type,
            'name' => $v->name,
            'interestRateYearly' => (float) $v->interest_rate_yearly,
            'principalAmount' => $v->principal_amount,
            'accruedInterestAmount' => $v->accrued_interest_amount,
            'totalBalance' => $v->totalBalance(),
            'termDays' => $v->term_days,
            'maturesAt' => $v->matures_at?->toIso8601String(),
            'autoCompound' => $v->auto_compound,
            'estimatedDailyInterest' => $v->estimatedDailyInterest(),
            'status' => $v->status,
        ];
    }
}
