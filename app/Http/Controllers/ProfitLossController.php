<?php

namespace App\Http\Controllers;

use App\Exports\ProfitLossExport;
use App\Http\Requests\ProfitLossRequest;
use App\Services\ProfitLossService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfitLossController extends Controller
{
    public function index(ProfitLossRequest $request, ProfitLossService $service): View
    {
        [$start, $end] = $this->resolvePeriod($request, $service);
        $search = trim($request->validated('search', ''));
        $report = $this->filterReport($service->report($start, $end), $search);

        return view('reports.profit_loss', [
            'report' => $report,
            'start' => $start,
            'end' => $end,
            'search' => $search,
        ]);
    }

    public function export(ProfitLossRequest $request, ProfitLossService $service): BinaryFileResponse
    {
        [$start, $end] = $this->resolvePeriod($request, $service);
        $search = trim($request->validated('search', ''));

        // Nama file memuat periode agar hasil unduhan mudah dibedakan.
        $filename = sprintf(
            'laporan-laba-rugi-%s-%s.xlsx',
            $start->format('Y-m'),
            $end->format('Y-m'),
        );

        return Excel::download(
            new ProfitLossExport($this->filterReport($service->report($start, $end), $search)),
            $filename,
        );
    }

    public function exportCsv(ProfitLossRequest $request, ProfitLossService $service): BinaryFileResponse
    {
        [$start, $end] = $this->resolvePeriod($request, $service);
        $search = trim($request->validated('search', ''));
        $filename = sprintf(
            'laporan-laba-rugi-%s-%s.csv',
            $start->format('Y-m'),
            $end->format('Y-m'),
        );

        return Excel::download(
            new ProfitLossExport($this->filterReport($service->report($start, $end), $search)),
            $filename,
            ExcelWriter::CSV,
        );
    }

    public function exportPdf(ProfitLossRequest $request, ProfitLossService $service): Response
    {
        [$start, $end] = $this->resolvePeriod($request, $service);
        $search = trim($request->validated('search', ''));
        $filename = sprintf(
            'laporan-laba-rugi-%s-%s.pdf',
            $start->format('Y-m'),
            $end->format('Y-m'),
        );

        return Pdf::loadView('reports.profit_loss_pdf', [
            'report' => $this->filterReport($service->report($start, $end), $search),
            'start' => $start,
            'end' => $end,
            'search' => $search,
        ])
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }

    /**
     * Filter monthly rows by their visible month label and recalculate totals
     * so the table and exported spreadsheet reflect the same search.
     *
     * @param  array{
     *     rows: Collection<int, array{month:string,label:string,income:float,expense:float,net:float}>,
     *     income_accounts: Collection<int, array{code:int,name:string,amounts:array<string,float>}>,
     *     expense_accounts: Collection<int, array{code:int,name:string,amounts:array<string,float>}>,
     *     total_income: float,
     *     total_expense: float,
     *     total_net: float
     * }  $report
     * @return array{
     *     rows: Collection<int, array{month:string,label:string,income:float,expense:float,net:float}>,
     *     income_accounts: Collection<int, array{code:int,name:string,amounts:array<string,float>}>,
     *     expense_accounts: Collection<int, array{code:int,name:string,amounts:array<string,float>}>,
     *     total_income: float,
     *     total_expense: float,
     *     total_net: float
     * }
     */
    private function filterReport(array $report, string $search): array
    {
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $report['rows'] = $report['rows']
                ->filter(fn (array $row): bool => str_contains(mb_strtolower($row['label']), $needle)
                    || str_contains($row['month'], $search))
                ->values();
            $report['total_income'] = round((float) $report['rows']->sum('income'), 2);
            $report['total_expense'] = round((float) $report['rows']->sum('expense'), 2);
            $report['total_net'] = round((float) $report['rows']->sum('net'), 2);
        }

        $monthKeys = array_fill_keys($report['rows']->pluck('month')->all(), true);
        $report['income_accounts'] = $this->filterAccountAmounts($report['income_accounts'], $monthKeys);
        $report['expense_accounts'] = $this->filterAccountAmounts($report['expense_accounts'], $monthKeys);

        return $report;
    }

    /**
     * Keep account amounts aligned with the months visible after report search.
     *
     * @param  Collection<int, array{code:int,name:string,amounts:array<string,float>}>  $accounts
     * @param  array<string, bool>  $monthKeys
     * @return Collection<int, array{code:int,name:string,amounts:array<string,float>}>
     */
    private function filterAccountAmounts(Collection $accounts, array $monthKeys): Collection
    {
        return $accounts->map(fn (array $account): array => [
            ...$account,
            'amounts' => array_intersect_key($account['amounts'], $monthKeys),
        ]);
    }

    /**
     * Menentukan periode laporan.
     *
     * Jika user tidak mengirim salah satu batas, batas tersebut diambil dari
     * bulan transaksi paling awal/akhir agar laporan langsung menampilkan data
     * yang ada. Jika belum ada transaksi sama sekali, dipakai bulan berjalan.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolvePeriod(ProfitLossRequest $request, ProfitLossService $service): array
    {
        $start = $request->filled('start_month')
            ? Carbon::createFromFormat('Y-m', $request->input('start_month'))->startOfMonth()
            : ($service->earliestTransactionMonth() ?? now()->startOfMonth());

        $end = $request->filled('end_month')
            ? Carbon::createFromFormat('Y-m', $request->input('end_month'))->endOfMonth()
            : ($service->latestTransactionMonth() ?? now()->endOfMonth());

        // Jaga agar rentang tetap valid ketika salah satu batas memakai default.
        // Kasus dua batas terisi namun terbalik sudah ditolak oleh FormRequest.
        if ($end->lt($start)) {
            if (! $request->filled('start_month')) {
                $start = $end->copy()->startOfMonth();
            } else {
                $end = $start->copy()->endOfMonth();
            }
        }

        return [$start, $end];
    }
}
