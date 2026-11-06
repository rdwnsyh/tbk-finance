<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\CoaCategory;
use App\Models\Transaction;
use App\Services\ProfitLossService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ProfitLossService $profitLossService): View
    {
        $start = $profitLossService->earliestTransactionMonth() ?? now()->startOfMonth();
        $end = $profitLossService->latestTransactionMonth() ?? now()->endOfMonth();
        $report = $profitLossService->report($start, $end);
        $trendRows = $report['rows'];
        $largestAmount = max(
            (float) $trendRows->max(fn (array $row): float => abs($row['income'])),
            (float) $trendRows->max(fn (array $row): float => abs($row['expense'])),
            1.0,
        );
        $trend = $trendRows->map(static function (array $row) use ($largestAmount): array {
            $toWidth = static fn (float $amount): int => $amount === 0.0
                ? 0
                : min(100, max(1, (int) round(abs($amount) / $largestAmount * 100)));

            return [
                ...$row,
                'income_width' => $toWidth($row['income']),
                'expense_width' => $toWidth($row['expense']),
            ];
        });

        return view('dashboard', [
            'overallReport' => $report,
            'transactionCount' => Transaction::query()->count(),
            'coaCount' => Coa::query()->count(),
            'categoryCount' => CoaCategory::query()->count(),
            'recentTransactions' => Transaction::query()
                ->with('coa:id,code,name')
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->paginate(6)
                ->withQueryString(),
            'trend' => $trend,
            'trendMonthCount' => $trend->count(),
        ]);
    }
}
