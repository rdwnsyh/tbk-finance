<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Halaman awal memang diarahkan ke daftar kategori COA.
     */
    public function test_the_home_page_redirects_to_coa_categories(): void
    {
        $this->get('/')->assertRedirect(route('coa-categories.index'));
    }
}
