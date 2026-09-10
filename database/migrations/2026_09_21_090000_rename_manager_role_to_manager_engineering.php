<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Renames the existing "manager" role in place (same row/id) so
     * assigned users and granted permissions carry over unchanged.
     */
    public function up(): void
    {
        DB::table('roles')->where('name', 'manager')->update(['name' => 'manager_engineering']);
    }

    public function down(): void
    {
        DB::table('roles')->where('name', 'manager_engineering')->update(['name' => 'manager']);
    }
};
