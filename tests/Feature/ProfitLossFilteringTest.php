<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\CoaCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfitLossFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_filters_month_rows_and_recalculates_totals(): void
    {
        $category = CoaCategory::create([
            'name' => 'Pendapatan',
            'type' => CoaCategory::TYPE_INCOME,
        ]);
        $coa = Coa::create([
            'code' => 4101,
            'name' => 'Penjualan',
            'coa_category_id' => $category->id,
        ]);
        Transaction::create([
            'date' => '2026-01-15',
            'coa_id' => $coa->id,
            'description' => 'Pendapatan Januari',
            'debit' => 0,
            'credit' => 100,
        ]);
        Transaction::create([
            'date' => '2026-02-15',
            'coa_id' => $coa->id,
            'description' => 'Pendapatan Februari',
            'debit' => 0,
            'credit' => 250,
        ]);

        $response = $this->get(route('profit-loss.index', [
            'start_month' => '2026-01',
            'end_month' => '2026-02',
            'search' => 'Februari',
        ]));

        $response->assertOk()
            ->assertSee('Februari 2026')
            ->assertDontSee('Januari 2026')
            ->assertSee('250,00')
            ->assertSee('search=Februari', false);
    }

    public function test_monthly_report_paginates_rows_and_preserves_filters(): void
    {
        $category = CoaCategory::create([
            'name' => 'Pendapatan',
            'type' => CoaCategory::TYPE_INCOME,
        ]);
        $coa = Coa::create([
            'code' => 4101,
            'name' => 'Penjualan',
            'coa_category_id' => $category->id,
        ]);

        foreach (range(1, 12) as $month) {
            Transaction::create([
                'date' => sprintf('2025-%02d-15', $month),
                'coa_id' => $coa->id,
                'description' => sprintf('Pendapatan bulan %02d', $month),
                'debit' => 0,
                'credit' => 100,
            ]);
        }

        $response = $this->get(route('profit-loss.index', [
            'start_month' => '2025-01',
            'end_month' => '2025-12',
            'search' => '2025',
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertSee('November 2025')
            ->assertSee('Desember 2025')
            ->assertDontSee('Oktober 2025')
            ->assertSee('1.200,00')
            ->assertSee('search=2025', false)
            ->assertSee('start_month=2025-01', false)
            ->assertSee('end_month=2025-12', false)
            ->assertSee('Previous')
            ->assertSee('Next');
    }
}
