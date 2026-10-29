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
        Schema::table('resignations', function (Blueprint $table) {
            // Experience certificate and relieving letter are company-authored
            // letters, so the system drafts them and HR can edit the text
            // before publishing it to the employee.
            $table->longText('experience_certificate_content')->nullable();
            $table->timestamp('experience_certificate_published_at')->nullable();
            $table->longText('relieving_letter_content')->nullable();
            $table->timestamp('relieving_letter_published_at')->nullable();

            // Form 16 and the UAN/PF document are real tax/government filings
            // (Form 16 in particular reflects actual TDS filed with the
            // Income Tax Department) — those aren't something this portal can
            // legitimately generate, so HR uploads the real file instead.
            $table->string('form16_path')->nullable();
            $table->string('uan_pf_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('resignations', function (Blueprint $table) {
            $table->dropColumn([
                'experience_certificate_content',
                'experience_certificate_published_at',
                'relieving_letter_content',
                'relieving_letter_published_at',
                'form16_path',
                'uan_pf_path',
            ]);
        });
    }
};
