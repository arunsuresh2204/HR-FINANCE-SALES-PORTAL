<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            ['date' => '2026-01-26', 'name' => 'Republic Day'],
            ['date' => '2026-02-15', 'name' => 'Maha Shivaratri'],
            ['date' => '2026-03-20', 'name' => 'Eid-ul-Fitr'],
            ['date' => '2026-04-03', 'name' => 'Good Friday'],
            ['date' => '2026-04-15', 'name' => 'Vishu'],
            ['date' => '2026-05-01', 'name' => 'May Day'],
            ['date' => '2026-05-27', 'name' => 'Bakrid (Eid-ul-Adha)'],
            ['date' => '2026-06-25', 'name' => 'Muharram'],
            ['date' => '2026-08-15', 'name' => 'Independence Day'],
            ['date' => '2026-08-25', 'name' => 'First Onam / Milad-i-Sherif'],
            ['date' => '2026-08-26', 'name' => 'Thiruvonam (Onam)'],
            ['date' => '2026-08-27', 'name' => 'Third Onam'],
            ['date' => '2026-08-28', 'name' => 'Fourth Onam / Sree Narayana Guru Jayanti'],
            ['date' => '2026-10-02', 'name' => 'Gandhi Jayanti'],
            ['date' => '2026-11-08', 'name' => 'Deepavali'],
            ['date' => '2026-12-25', 'name' => 'Christmas'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(['date' => $holiday['date']], $holiday);
        }
    }
}
