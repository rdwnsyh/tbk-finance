<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\CoaCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_matches_description_and_coa_details(): void
    {
        $category = CoaCategory::create([
            'name' => 'Beban',
            'type' => CoaCategory::TYPE_EXPENSE,
        ]);
        $coa = Coa::create([
            'code' => 6101,
            'name' => 'Internet Kantor',
            'coa_category_id' => $category->id,
        ]);
        Transaction::create([
            'date' => '2026-03-10',
            'coa_id' => $coa->id,
            'description' => 'Tagihan internet bulan Maret',
            'debit' => 500000,
            'credit' => 0,
        ]);

        foreach (['Tagihan internet', '6101', 'Internet Kantor'] as $search) {
            $this->get(route('transactions.index', ['search' => $search]))
                ->assertOk()
                ->assertSee('Tagihan internet bulan Maret');
        }

        $this->get(route('transactions.index', ['search' => 'Tidak ditemukan']))
            ->assertOk()
            ->assertSee('Tidak ada transaksi yang cocok dengan filter.');
    }

    public function test_date_range_filters_transactions_and_is_kept_in_pagination(): void
    {
        $category = CoaCategory::create([
            'name' => 'Beban',
            'type' => CoaCategory::TYPE_EXPENSE,
        ]);
        $coa = Coa::create([
            'code' => 6101,
            'name' => 'Operasional',
            'coa_category_id' => $category->id,
        ]);

        foreach (range(1, 12) as $day) {
            Transaction::create([
                'date' => sprintf('2026-03-%02d', $day),
                'coa_id' => $coa->id,
                'description' => sprintf('Dalam periode %02d', $day),
                'debit' => 1000,
                'credit' => 0,
            ]);
        }
        Transaction::create([
            'date' => '2026-02-28',
            'coa_id' => $coa->id,
            'description' => 'Di luar periode',
            'debit' => 1000,
            'credit' => 0,
        ]);

        $response = $this->get(route('transactions.index', [
            'date_from' => '2026-03-01',
            'date_to' => '2026-03-31',
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertSee('Dalam periode 02')
            ->assertSee('Dalam periode 01')
            ->assertDontSee('Di luar periode')
            ->assertSee('date_from=2026-03-01', false)
            ->assertSee('date_to=2026-03-31', false);
    }
}
