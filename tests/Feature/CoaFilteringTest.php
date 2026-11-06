<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\CoaCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoaFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_matches_coa_code_name_and_category(): void
    {
        $category = CoaCategory::create([
            'name' => 'Biaya Pemasaran',
            'type' => CoaCategory::TYPE_EXPENSE,
        ]);
        Coa::create([
            'code' => 6101,
            'name' => 'Iklan Digital',
            'coa_category_id' => $category->id,
        ]);

        foreach (['6101', 'Iklan Digital', 'Pemasaran'] as $search) {
            $this->get(route('coas.index', ['search' => $search]))
                ->assertOk()
                ->assertSee('Iklan Digital');
        }

        $this->get(route('coas.index', ['search' => 'Tidak ditemukan']))
            ->assertOk()
            ->assertSee('Tidak ada COA yang cocok dengan filter.');
    }

    public function test_type_filter_limits_coas_to_the_selected_category_type(): void
    {
        $incomeCategory = CoaCategory::create([
            'name' => 'Pendapatan',
            'type' => CoaCategory::TYPE_INCOME,
        ]);
        $expenseCategory = CoaCategory::create([
            'name' => 'Beban',
            'type' => CoaCategory::TYPE_EXPENSE,
        ]);
        Coa::create([
            'code' => 4101,
            'name' => 'Penjualan',
            'coa_category_id' => $incomeCategory->id,
        ]);
        Coa::create([
            'code' => 6101,
            'name' => 'Operasional',
            'coa_category_id' => $expenseCategory->id,
        ]);

        $this->get(route('coas.index', ['type' => CoaCategory::TYPE_EXPENSE]))
            ->assertOk()
            ->assertSee('Operasional')
            ->assertDontSee('Penjualan');
    }

    public function test_coa_pagination_keeps_search_and_type_filters(): void
    {
        $category = CoaCategory::create([
            'name' => 'Pendapatan',
            'type' => CoaCategory::TYPE_INCOME,
        ]);

        foreach (range(1, 11) as $number) {
            Coa::create([
                'code' => 4000 + $number,
                'name' => sprintf('Penjualan %02d', $number),
                'coa_category_id' => $category->id,
            ]);
        }

        $this->get(route('coas.index', [
            'search' => 'Penjualan',
            'type' => CoaCategory::TYPE_INCOME,
            'page' => 2,
        ]))
            ->assertOk()
            ->assertSee('Penjualan 11')
            ->assertSee('search=Penjualan', false)
            ->assertSee('type=income', false)
            ->assertSee('page=1', false)
            ->assertSee('Previous');

        $this->get(route('coas.index', [
            'search' => 'Penjualan',
            'type' => CoaCategory::TYPE_INCOME,
        ]))
            ->assertOk()
            ->assertSee('page=2', false)
            ->assertSee('Next');
    }
}
