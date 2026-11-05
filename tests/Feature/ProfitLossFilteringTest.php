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
}
