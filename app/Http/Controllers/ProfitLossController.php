<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfitLossRequest;
use App\Services\ProfitLossService;
use Carbon\Carbon;
use Illuminate\View\View;

class ProfitLossController extends Controller
{
    public function index(ProfitLossRequest $request, ProfitLossService $service): View
    {
        [$start, $end] = $this->resolvePeriod($request, $service);

        return view('reports.profit_loss', [
            'report' => $service->report($start, $end),
            'start' => $start,
            'end' => $end,
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
