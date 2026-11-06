<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithEvents, WithStrictNullComparison, WithStyles, WithTitle
{
    private array $rows;

    /**
     * @var array<int, string>
     */
    private array $styledRows = [];

    /**
     * @param  array{
     *     rows: Collection<int, array{month:string,label:string,income:float,expense:float,net:float}>,
     *     income_accounts: Collection<int, array{code:int,name:string,amounts:array<string,float>}>,
     *     expense_accounts: Collection<int, array{code:int,name:string,amounts:array<string,float>}>,
     *     total_income: float,
     *     total_expense: float,
     *     total_net: float
     * }  $report
     */
    public function __construct(private readonly array $report)
    {
        $this->rows = $this->buildRows();
    }

    /**
     * @return array<int, array<int, int|float|string>>
     */
    public function array(): array
    {
        return $this->rows;
    }

    public function columnFormats(): array
    {
        $formats = [];

        for ($column = 2; $column <= $this->report['rows']->count() + 1; $column++) {
            $formats[Coordinate::stringFromColumnIndex($column)] = 'General';
        }

        return $formats;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $event->sheet->getDelegate()->mergeCells('A2:A3');
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
            ],
            3 => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
            ],
        ];

        foreach ($this->styledRows as $row => $type) {
            $styles[$row] = match ($type) {
                'income' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6E0B4']]],
                'income_total' => [
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'A9D18E']],
                ],
                'expense' => ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8CBAD']]],
                'expense_total' => [
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4B183']],
                ],
                'net' => ['font' => ['bold' => true]],
            };
        }

        return $styles;
    }

    public function title(): string
    {
        return 'Laba Rugi';
    }

    /**
     * @return array<int, array<int, int|float|string>>
     */
    private function buildRows(): array
    {
        $months = $this->report['rows']->pluck('month')->all();
        $monthlyRows = $this->report['rows']->keyBy('month');
        $rows = [
            ['Laporan Profit/Loss'],
            array_merge(['Category'], $months),
            array_merge([''], array_fill(0, count($months), 'Amount')),
        ];

        foreach ($this->report['income_accounts'] as $account) {
            $rows[] = $this->accountRow($account, $months);
            $this->styledRows[count($rows)] = 'income';
        }

        $rows[] = $this->totalRow('Total Income', $months, $monthlyRows, 'income');
        $this->styledRows[count($rows)] = 'income_total';

        foreach ($this->report['expense_accounts'] as $account) {
            $rows[] = $this->accountRow($account, $months);
            $this->styledRows[count($rows)] = 'expense';
        }

        $rows[] = $this->totalRow('Total Expense', $months, $monthlyRows, 'expense');
        $this->styledRows[count($rows)] = 'expense_total';

        $rows[] = $this->totalRow('Net Income', $months, $monthlyRows, 'net');
        $this->styledRows[count($rows)] = 'net';

        return $rows;
    }

    /**
     * @param  array{code:int,name:string,amounts:array<string,float>}  $account
     * @param  array<int, string>  $months
     * @return array<int, int|float|string>
     */
    private function accountRow(array $account, array $months): array
    {
        $row = [$account['name']];

        foreach ($months as $month) {
            $row[] = $account['amounts'][$month] ?? 0;
        }

        return $row;
    }

    /**
     * @param  array<int, string>  $months
     * @param  Collection<string, array{month:string,label:string,income:float,expense:float,net:float}>  $monthlyRows
     * @param  'income'|'expense'|'net'  $amountType
     * @return array<int, int|float|string>
     */
    private function totalRow(string $label, array $months, Collection $monthlyRows, string $amountType): array
    {
        $row = [$label];

        foreach ($months as $month) {
            $row[] = $monthlyRows->get($month)[$amountType] ?? 0;
        }

        return $row;
    }
}
