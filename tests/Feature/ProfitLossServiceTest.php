<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\CoaCategory;
use App\Models\Transaction;
use App\Services\ProfitLossService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitLossServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProfitLossService $service;

    private Coa $incomeCoa;

    private Coa $expenseCoa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ProfitLossService::class);

        $incomeCategory = CoaCategory::create([
            'name' => 'Pendapatan',
            'type' => CoaCategory::TYPE_INCOME,
        ]);

        $expenseCategory = CoaCategory::create([
            'name' => 'Beban',
            'type' => CoaCategory::TYPE_EXPENSE,
        ]);

        $this->incomeCoa = Coa::create([
            'code' => 401,
            'name' => 'Pendapatan Usaha',
            'coa_category_id' => $incomeCategory->id,
        ]);

        $this->expenseCoa = Coa::create([
            'code' => 601,
            'name' => 'Beban Operasional',
            'coa_category_id' => $expenseCategory->id,
        ]);
    }

    public function test_it_calculates_monthly_net_income_and_totals(): void
    {
        // Januari: income 10.000 + 2.000 (tanggal terakhir) - 1.000 (debit),
        // expense 2.500 - 500 (credit) = 2.000.
        $this->createTransaction('2022-01-05', $this->incomeCoa, credit: 10000);
        $this->createTransaction('2022-01-10', $this->expenseCoa, debit: 2500);
        $this->createTransaction('2022-01-15', $this->incomeCoa, debit: 1000);
        $this->createTransaction('2022-01-20', $this->expenseCoa, credit: 500);
        $this->createTransaction('2022-01-31', $this->incomeCoa, credit: 2000);

        // Februari: income 3.000, expense 1.000 (tanggal terakhir).
        $this->createTransaction('2022-02-01', $this->incomeCoa, credit: 3000);
        $this->createTransaction('2022-02-28', $this->expenseCoa, debit: 1000);

        $report = $this->service->report(
            Carbon::parse('2022-01-01'),
            Carbon::parse('2022-03-31'),
        );

        $this->assertCount(3, $report['rows']);

        $january = $report['rows']->firstWhere('month', '2022-01');
        $this->assertNotNull($january);
        $this->assertSame('Januari 2022', $january['label']);
        $this->assertEqualsWithDelta(11000.0, (float) $january['income'], 0.001);
        $this->assertEqualsWithDelta(2000.0, (float) $january['expense'], 0.001);
        $this->assertEqualsWithDelta(9000.0, (float) $january['net'], 0.001);

        $february = $report['rows']->firstWhere('month', '2022-02');
        $this->assertNotNull($february);
        $this->assertEqualsWithDelta(3000.0, (float) $february['income'], 0.001);
        $this->assertEqualsWithDelta(1000.0, (float) $february['expense'], 0.001);
        $this->assertEqualsWithDelta(2000.0, (float) $february['net'], 0.001);

        $march = $report['rows']->firstWhere('month', '2022-03');
        $this->assertNotNull($march);
        $this->assertEqualsWithDelta(0.0, (float) $march['income'], 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $march['expense'], 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $march['net'], 0.001);

        $this->assertEqualsWithDelta(14000.0, (float) $report['total_income'], 0.001);
        $this->assertEqualsWithDelta(3000.0, (float) $report['total_expense'], 0.001);
        $this->assertEqualsWithDelta(11000.0, (float) $report['total_net'], 0.001);
    }

    public function test_it_returns_zero_values_when_the_period_has_no_transactions(): void
    {
        $report = $this->service->report(
            Carbon::parse('2021-11-01'),
            Carbon::parse('2021-12-31'),
        );

        $this->assertCount(2, $report['rows']);
        $this->assertSame(
            ['November 2021', 'Desember 2021'],
            $report['rows']->pluck('label')->all(),
        );

        $this->assertEqualsWithDelta(0.0, (float) $report['total_income'], 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $report['total_expense'], 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $report['total_net'], 0.001);
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
            'description' => 'Transaksi uji '.$date,
            'debit' => $debit,
            'credit' => $credit,
        ]);
    }
}
