<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // create_coa_categories_table
        Schema::create('coa_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->enum('type', ['income', 'expense']);   // dibutuhkan untuk Total Income/Expense
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coa_categories');
    }
};
