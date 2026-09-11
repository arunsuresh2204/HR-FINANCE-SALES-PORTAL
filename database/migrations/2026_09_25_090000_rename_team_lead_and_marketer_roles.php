<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Renames these two core roles in place (same row/id) so assigned
     * users and granted permissions carry over unchanged.
     */
    protected const RENAMES = [
        'team_lead' => 'team_lead_it',
        'marketer' => 'digital_marketer',
    ];

    public function up(): void
    {
        foreach (self::RENAMES as $from => $to) {
            DB::table('roles')->where('name', $from)->update(['name' => $to]);
        }
    }

    public function down(): void
    {
        foreach (self::RENAMES as $from => $to) {
            DB::table('roles')->where('name', $to)->update(['name' => $from]);
        }
    }
};
