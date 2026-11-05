<?php

namespace App\Services;

use App\Models\CoaCategory;
use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ProfitLossService
{
    /**
     * Nama bulan Indonesia. Disimpan eksplisit agar label laporan tidak
     * bergantung pada locale global aplikasi.
     */
    private const MONTH_NAMES = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Menghitung laporan laba/rugi per bulan.
     *
     * Pendapatan = credit - debit pada COA bertipe income.
     * Beban      = debit - credit pada COA bertipe expense.
     *
     * Agregasi dilakukan di PHP agar query tetap kompatibel dengan MySQL
     * (aplikasi) dan SQLite (test), tanpa fungsi tanggal khusus vendor.
     *
     * @return array{
     *     rows: Collection<int, array{month:string,label:string,income:float,expense:float,net:float}>,
     *     total_income: float,
     *     total_expense: float,
     *     total_net: float
     * }
     */
    public function report(CarbonInterface $start, CarbonInterface $end): array
    {
        $periodStart = Carbon::parse($start)->startOfMonth();
        $periodEnd = Carbon::parse($end)->endOfMonth();

        // Eager load relasi agar tipe kategori COA tidak memicu N+1.
        //
        // Batas memakai awal/akhir hari (bukan hanya tanggal): di SQLite kolom
        // DATE disimpan sebagai teks 'Y-m-d H:i:s', sehingga transaksi pada
        // tanggal terakhir bulan bisa terlewat jika batas atasnya 'Y-m-d'.
        $transactions = Transaction::query()
            ->with('coa.coaCategory')
            ->whereBetween('date', [
                $periodStart->toDateTimeString(),
                $periodEnd->toDateTimeString(),
            ])
            ->get(['id', 'date', 'coa_id', 'debit', 'credit']);

        // Siapkan semua bulan dalam rentang. Bulan tanpa transaksi tetap
        // tampil dengan nilai 0 supaya laporan tidak "bolong".
        $rows = [];
        $cursor = $periodStart->copy();

        while ($cursor->lte($periodEnd)) {
            $key = $cursor->format('Y-m');

            $rows[$key] = [
                'month' => $key,
                'label' => $this->monthLabel($cursor),
                'income' => 0.0,
                'expense' => 0.0,
                'net' => 0.0,
            ];

            $cursor->addMonthNoOverflow();
        }

        foreach ($transactions as $transaction) {
            $key = $transaction->date->format('Y-m');

            if (! isset($rows[$key])) {
                continue;
            }

            $type = $transaction->coa->coaCategory->type;
            $debit = (float) $transaction->debit;
            $credit = (float) $transaction->credit;

            if ($type === CoaCategory::TYPE_INCOME) {
                $rows[$key]['income'] += $credit - $debit;
            } else {
                $rows[$key]['expense'] += $debit - $credit;
            }
        }

        foreach ($rows as &$row) {
            $row['net'] = round($row['income'] - $row['expense'], 2);
        }
        unset($row);

        $rows = collect($rows)->values();

        return [
            'rows' => $rows,
            'total_income' => round((float) $rows->sum('income'), 2),
            'total_expense' => round((float) $rows->sum('expense'), 2),
            'total_net' => round((float) $rows->sum('net'), 2),
        ];
    }

    /**
     * Bulan transaksi paling awal; null jika belum ada transaksi.
     */
    public function earliestTransactionMonth(): ?Carbon
    {
        $date = Transaction::query()->min('date');

        return $date ? Carbon::parse($date)->startOfMonth() : null;
    }

    /**
     * Bulan transaksi paling akhir; null jika belum ada transaksi.
     */
    public function latestTransactionMonth(): ?Carbon
    {
        $date = Transaction::query()->max('date');

        return $date ? Carbon::parse($date)->endOfMonth() : null;
    }

    private function monthLabel(CarbonInterface $date): string
    {
        return self::MONTH_NAMES[(int) $date->format('n')].' '.$date->format('Y');
    }
}
