<?php

namespace Tests\Feature;

use App\Models\Coa;
use App\Models\CoaCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionValidationTest extends TestCase
{
    use RefreshDatabase;

    private Coa $coa;

    protected function setUp(): void
    {
        parent::setUp();

        $category = CoaCategory::create([
            'name' => 'Pendapatan',
            'type' => CoaCategory::TYPE_INCOME,
        ]);

        $this->coa = Coa::create([
            'code' => 401,
            'name' => 'Pendapatan Usaha',
            'coa_category_id' => $category->id,
        ]);
    }

    public function test_it_rejects_a_transaction_with_both_debit_and_credit(): void
    {
        $response = $this->post(route('transactions.store'), $this->payload([
            'debit' => 100,
            'credit' => 200,
        ]));

        $response->assertSessionHasErrors([
            'amount' => 'Isi salah satu: debit atau credit, tidak keduanya.',
        ]);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_it_rejects_a_transaction_with_zero_debit_and_credit(): void
    {
        $response = $this->post(route('transactions.store'), $this->payload([
            'debit' => 0,
            'credit' => 0,
        ]));

        $response->assertSessionHasErrors([
            'amount' => 'Nominal debit atau credit harus lebih dari 0.',
        ]);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_it_rejects_a_coa_that_does_not_exist(): void
    {
        $response = $this->post(route('transactions.store'), $this->payload([
            'coa_id' => 999999,
            'debit' => 100,
            'credit' => 0,
        ]));

        $response->assertSessionHasErrors([
            'coa_id' => 'COA yang dipilih tidak valid.',
        ]);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_it_stores_a_transaction_with_only_debit(): void
    {
        $response = $this->post(route('transactions.store'), $this->payload([
            'debit' => 500,
            'credit' => 0,
        ]));

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('transactions', [
            'coa_id' => $this->coa->id,
            'debit' => 500,
            'credit' => 0,
        ]);
    }

    public function test_it_stores_a_transaction_with_only_credit(): void
    {
        $response = $this->post(route('transactions.store'), $this->payload([
            'debit' => 0,
            'credit' => 750,
        ]));

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('transactions', [
            'coa_id' => $this->coa->id,
            'debit' => 0,
            'credit' => 750,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'date' => '2022-01-31',
            'coa_id' => $this->coa->id,
            'description' => 'Transaksi uji',
            'debit' => 0,
            'credit' => 0,
        ], $overrides);
    }
}
