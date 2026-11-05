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
        // create_coas_table
        Schema::create('coas', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('code')->unique();
            $t->string('name');
            $t->foreignId('coa_category_id')->constrained('coa_categories')->restrictOnDelete();
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coas');
    }
};
