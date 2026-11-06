<?php

namespace Tests\Feature;

use App\Exports\ProfitLossExport;
use App\Models\Coa;
use App\Models\CoaCategory;
use App\Models\Transaction;
use App\Services\ProfitLossService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

class ProfitLossExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_downloaded_report_matches_the_month_by_account_matrix(): void
    {
        $incomeCategory = CoaCategory::create([
            'name' => 'Pendapatan',
            'type' => CoaCategory::TYPE_INCOME,
        ]);
        $expenseCategory = CoaCategory::create([
            'name' => 'Pengeluaran',
            'type' => CoaCategory::TYPE_EXPENSE,
        ]);

        $salary = $this->createCoa(401, 'Salary', $incomeCategory);
        $otherIncome = $this->createCoa(402, 'Other Income', $incomeCategory);
        $familyExpense = $this->createCoa(601, 'Family Expense', $expenseCategory);
        $transportExpense = $this->createCoa(602, 'Transport Expense', $expenseCategory);
        $mealExpense = $this->createCoa(603, 'Meal Expense', $expenseCategory);

        $this->createTransaction('2022-01-05', $salary, credit: 12000000);
        $this->createTransaction('2022-02-05', $salary, credit: 12000000);
        $this->createTransaction('2022-03-05', $salary, credit: 12000000);
        $this->createTransaction('2022-01-10', $otherIncome, credit: 5500000);
        $this->createTransaction('2022-02-10', $otherIncome, credit: 6000000);
        $this->createTransaction('2022-03-10', $otherIncome, credit: 3500000);
        $this->createTransaction('2022-01-11', $familyExpense, debit: 500000);
        $this->createTransaction('2022-02-11', $familyExpense, debit: 3500000);
        $this->createTransaction('2022-03-11', $familyExpense, debit: 4500000);
        $this->createTransaction('2022-01-12', $transportExpense, debit: 200000);
        $this->createTransaction('2022-02-12', $transportExpense, debit: 250000);
        $this->createTransaction('2022-03-12', $transportExpense, debit: 225000);
        $this->createTransaction('2022-01-13', $mealExpense, debit: 150000);
        $this->createTransaction('2022-02-13', $mealExpense, debit: 300000);
        $this->createTransaction('2022-03-13', $mealExpense, debit: 175000);

        $report = app(ProfitLossService::class)->report(
            Carbon::parse('2022-01-01'),
            Carbon::parse('2022-03-31'),
        );
        $export = new ProfitLossExport($report);
        $expectedRows = [
            ['Laporan Profit/Loss'],
            ['Category', '2022-01', '2022-02', '2022-03'],
            ['', 'Amount', 'Amount', 'Amount'],
            ['Salary', 12000000.0, 12000000.0, 12000000.0],
            ['Other Income', 5500000.0, 6000000.0, 3500000.0],
            ['Total Income', 17500000.0, 18000000.0, 15500000.0],
            ['Family Expense', 500000.0, 3500000.0, 4500000.0],
            ['Transport Expense', 200000.0, 250000.0, 225000.0],
            ['Meal Expense', 150000.0, 300000.0, 175000.0],
            ['Total Expense', 850000.0, 4050000.0, 4900000.0],
            ['Net Income', 16650000.0, 13950000.0, 10600000.0],
        ];

        $this->assertSame($expectedRows, $export->array());

        $temporaryFile = tempnam(sys_get_temp_dir(), 'profit-loss-');
        $this->assertNotFalse($temporaryFile);

        try {
            file_put_contents($temporaryFile, Excel::raw($export, ExcelWriter::XLSX));
            $sheet = IOFactory::load($temporaryFile)->getActiveSheet();

            $this->assertSame('2022-01', $sheet->getCell('B2')->getValue());
            $this->assertSame('Amount', $sheet->getCell('B3')->getValue());
            $this->assertSame('Salary', $sheet->getCell('A4')->getValue());
            $this->assertSame('12000000', $sheet->getCell('B4')->getFormattedValue());
            $this->assertArrayHasKey('A2:A3', $sheet->getMergeCells());
            $this->assertSame('C6E0B4', $sheet->getStyle('A4')->getFill()->getStartColor()->getRGB());
        } finally {
            if (file_exists($temporaryFile)) {
                unlink($temporaryFile);
            }
        }

        Excel::fake();

        $response = $this->get(route('profit-loss.export', [
            'start_month' => '2022-01',
            'end_month' => '2022-03',
        ]));

        $response->assertOk();
        Excel::assertDownloaded(
            'laporan-laba-rugi-2022-01-2022-03.xlsx',
            function (ProfitLossExport $export) use ($expectedRows): bool {
                $this->assertSame($expectedRows, $export->array());
                $this->assertSame(['B' => 'General', 'C' => 'General', 'D' => 'General'], $export->columnFormats());
                $styles = $export->styles(new Worksheet);
                $this->assertSame(Fill::FILL_SOLID, $styles[2]['fill']['fillType']);
                $this->assertSame('FFFF00', $styles[2]['fill']['startColor']['rgb']);
                $this->assertSame('C6E0B4', $styles[4]['fill']['startColor']['rgb']);
                $this->assertSame('A9D18E', $styles[6]['fill']['startColor']['rgb']);
                $this->assertSame('F8CBAD', $styles[7]['fill']['startColor']['rgb']);
                $this->assertSame('F4B183', $styles[10]['fill']['startColor']['rgb']);

                return true;
            },
        );

        Excel::fake();

        $this->get(route('profit-loss.export', [
            'start_month' => '2022-01',
            'end_month' => '2022-03',
            'search' => 'Februari',
        ]))->assertOk();

        Excel::assertDownloaded(
            'laporan-laba-rugi-2022-01-2022-03.xlsx',
            function (ProfitLossExport $export): bool {
                $this->assertSame([
                    ['Laporan Profit/Loss'],
                    ['Category', '2022-02'],
                    ['', 'Amount'],
                    ['Salary', 12000000.0],
                    ['Other Income', 6000000.0],
                    ['Total Income', 18000000.0],
                    ['Family Expense', 3500000.0],
                    ['Transport Expense', 250000.0],
                    ['Meal Expense', 300000.0],
                    ['Total Expense', 4050000.0],
                    ['Net Income', 13950000.0],
                ], $export->array());

                return true;
            },
        );
    }

    private function createCoa(int $code, string $name, CoaCategory $category): Coa
    {
        return Coa::create([
            'code' => $code,
            'name' => $name,
            'coa_category_id' => $category->id,
        ]);
    }

    private function createTransaction(
        string $date,
        Coa $coa,
        float $debit = 0,
        float $credit = 0,
    ): void {
        Transaction::create([
            'date' => $date,
            'coa_id' => $coa->id,
            'debit' => $debit,
            'credit' => $credit,
        ]);
    }
}
