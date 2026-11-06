<?php

namespace Tests\Feature;

use App\Models\CoaCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoaCategoryFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_and_type_filters_only_matching_categories(): void
    {
        CoaCategory::create([
            'name' => 'Pendapatan Jasa',
            'type' => CoaCategory::TYPE_INCOME,
        ]);
        CoaCategory::create([
            'name' => 'Beban Jasa',
            'type' => CoaCategory::TYPE_EXPENSE,
        ]);

        $response = $this->get(route('coa-categories.index', [
            'search' => 'Jasa',
            'type' => CoaCategory::TYPE_INCOME,
        ]));

        $response->assertOk()
            ->assertSee('Pendapatan Jasa')
            ->assertDontSee('Beban Jasa');
    }

    public function test_pagination_keeps_the_category_search_filter(): void
    {
        foreach (range(1, 11) as $number) {
            CoaCategory::create([
                'name' => sprintf('Kategori Pencarian %02d', $number),
                'type' => CoaCategory::TYPE_INCOME,
            ]);
        }

        $response = $this->get(route('coa-categories.index', [
            'search' => 'Kategori Pencarian',
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertSee('search=Kategori%20Pencarian', false)
            ->assertSee('Previous');

        $this->get(route('coa-categories.index', [
            'search' => 'Kategori Pencarian',
        ]))
            ->assertOk()
            ->assertSee('page=2', false)
            ->assertSee('Next');
    }
}
