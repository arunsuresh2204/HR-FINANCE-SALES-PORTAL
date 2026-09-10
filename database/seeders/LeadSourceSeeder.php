<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use Illuminate\Database\Seeder;

class LeadSourceSeeder extends Seeder
{
    public const SOURCES = [
        'Upwork',
        'LinkedIn',
        'Cold Email',
        'Cold Call',
        'Referral',
        'Instagram',
        'Google Ads',
        'Freelancer.com',
    ];

    public function run(): void
    {
        foreach (self::SOURCES as $source) {
            LeadSource::firstOrCreate(['name' => $source]);
        }
    }
}
