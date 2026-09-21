<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('symbol', 8);
            $table->boolean('symbol_spaced')->default(false);
            $table->enum('format_style', ['standard', 'european', 'indian'])->default('standard');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the currencies the app already supported, so existing INR/USD/EUR
        // data keeps formatting exactly as before once Currency reads from this table.
        DB::table('currencies')->insert([
            ['code' => 'INR', 'symbol' => '₹', 'symbol_spaced' => false, 'format_style' => 'indian', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'USD', 'symbol' => '$', 'symbol_spaced' => false, 'format_style' => 'standard', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'EUR', 'symbol' => '€', 'symbol_spaced' => true, 'format_style' => 'european', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
