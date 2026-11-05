<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * WithStrictNullComparison wajib dipakai agar nilai 0 tetap ditulis.
 * Tanpa concern ini PhpSpreadsheet memakai perbandingan longgar (0 == null),
 * sehingga bulan tanpa transaksi tampil sebagai sel kosong.
 */
class ProfitLossExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles, WithTitle
{
    /**
     * @param  array{
     *     rows: Collection<int, array{month:string,label:string,income:float,expense:float,net:float}>,
     *     total_income: float,
     *     total_expense: float,
     *     total_net: float
     * }  $report
     */
    public function __construct(private readonly array $report) {}

    public function collection(): Collection
    {
        // Data laporan sudah dihitung sekali oleh ProfitLossService di
        // controller, jadi export tidak perlu query ulang ke database.
        // Baris TOTAL ditempel di akhir agar isi Excel sama dengan tabel.
        return $this->report['rows']
            ->map(fn (array $row) => [
                'label' => $row['label'],
                'income' => $row['income'],
                'expense' => $row['expense'],
                'net' => $row['net'],
            ])
            ->push([
                'label' => 'TOTAL',
                'income' => $this->report['total_income'],
                'expense' => $this->report['total_expense'],
                'net' => $this->report['total_net'],
            ]);
    }

    public function headings(): array
    {
        return ['Bulan', 'Pendapatan', 'Beban', 'Laba/Rugi Bersih'];
    }

    /**
     * @param  array{label:string,income:float,expense:float,net:float}  $row
     */
    public function map($row): array
    {
        return [$row['label'], $row['income'], $row['expense'], $row['net']];
    }

    public function columnFormats(): array
    {
        // Format angka standar Excel; tampilan pemisah ribuan/desimal
        // mengikuti locale komputer yang membuka file.
        return [
            'B' => '#,##0.00',
            'C' => '#,##0.00',
            'D' => '#,##0.00',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Heading di baris 1 dan baris TOTAL (setelah semua bulan) ditebalkan.
        $totalRow = $this->report['rows']->count() + 2;

        return [
            1 => ['font' => ['bold' => true]],
            $totalRow => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Laba Rugi';
    }
}
