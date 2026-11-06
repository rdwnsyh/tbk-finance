<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\CoaCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_all_time_totals_chart_and_recent_transactions(): void
    {
        $this->travelTo('2026-03-15 12:00:00');

        $incomeCategory = CoaCategory::create([
            'name' => 'Pendapatan',
            'type' => CoaCategory::TYPE_INCOME,
        ]);
        $expenseCategory = CoaCategory::create([
            'name' => 'Beban',
            'type' => CoaCategory::TYPE_EXPENSE,
        ]);
        $incomeCoa = Coa::create([
            'code' => 401,
            'name' => 'Pendapatan Usaha',
            'coa_category_id' => $incomeCategory->id,
        ]);
        $expenseCoa = Coa::create([
            'code' => 601,
            'name' => 'Beban Operasional',
            'coa_category_id' => $expenseCategory->id,
        ]);
        Transaction::create([
            'date' => '2026-03-01',
            'coa_id' => $incomeCoa->id,
            'description' => 'Penjualan bulan ini',
            'debit' => 0,
            'credit' => 2000000,
        ]);
        Transaction::create([
            'date' => '2026-03-31',
            'coa_id' => $expenseCoa->id,
            'description' => 'Beban akhir bulan',
            'debit' => 500000,
            'credit' => 0,
        ]);
        Transaction::create([
            'date' => '2025-03-15',
            'coa_id' => $incomeCoa->id,
            'description' => 'Transaksi tahun sebelumnya',
            'debit' => 0,
            'credit' => 9000000,
        ]);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertViewIs('dashboard')
            ->assertSee('Rp 11.000.000')
            ->assertSee('Rp 500.000')
            ->assertSee('Rp 10.500.000')
            ->assertSee('Akumulasi seluruh periode')
            ->assertSee('Ringkasan seluruh periode transaksi')
            ->assertSee('13 bulan')
            ->assertSee('Penjualan bulan ini')
            ->assertSee('Beban akhir bulan')
            ->assertSee('Transaksi tahun sebelumnya')
            ->assertSee('<strong>3</strong>', false)
            ->assertSee('2 akun COA terdaftar')
            ->assertSee('title="Pendapatan Maret 2025: Rp 9.000.000"', false)
            ->assertSee('style="width: 22%"', false)
            ->assertSee('style="width: 6%"', false)
            ->assertSee('style="width: 100%"', false)
            ->assertSee('style="width: 0%"', false);
    }

    public function test_dashboard_chart_shows_the_magnitude_of_a_negative_income_value(): void
    {
        $this->travelTo('2026-03-15 12:00:00');

        $incomeCategory = CoaCategory::create([
            'name' => 'Pendapatan',
            'type' => CoaCategory::TYPE_INCOME,
        ]);
        $incomeCoa = Coa::create([
            'code' => 401,
            'name' => 'Pendapatan Usaha',
            'coa_category_id' => $incomeCategory->id,
        ]);
        Transaction::create([
            'date' => '2026-03-10',
            'coa_id' => $incomeCoa->id,
            'description' => 'Koreksi pendapatan',
            'debit' => 1000,
            'credit' => 0,
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rp -1.000')
            ->assertSee('title="Pendapatan Maret 2026: Rp -1.000"', false)
            ->assertSee('style="width: 100%"', false);
    }

    public function test_dashboard_transactions_can_be_browsed_with_previous_and_next_links(): void
    {
        $category = CoaCategory::create([
            'name' => 'Beban',
            'type' => CoaCategory::TYPE_EXPENSE,
        ]);
        $coa = Coa::create([
            'code' => 601,
            'name' => 'Beban Operasional',
            'coa_category_id' => $category->id,
        ]);

        foreach (range(1, 7) as $day) {
            Transaction::create([
                'date' => sprintf('2026-03-%02d', $day),
                'coa_id' => $coa->id,
                'description' => sprintf('Transaksi %02d', $day),
                'debit' => 1000,
                'credit' => 0,
            ]);
        }

        $this->get(route('dashboard', ['page' => 2]))
            ->assertOk()
            ->assertSee('Transaksi 01')
            ->assertDontSee('Transaksi 07')
            ->assertSee('page=1', false)
            ->assertSee('Previous');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Transaksi 07')
            ->assertSee('page=2', false)
            ->assertSee('Next');
    }
}
