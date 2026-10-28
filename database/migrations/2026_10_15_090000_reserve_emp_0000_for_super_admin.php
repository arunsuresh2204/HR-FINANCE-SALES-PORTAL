<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The Super Admin's employee_code is reserved as EMP-0000 — a sentinel
     * that sorts first and visually distinguishes the root account, while
     * they otherwise remain a normal employee (attendance, leave, payslips).
     * New hires are numbered from EMP-0001 by HrAdmin\EmployeeIndex, which
     * already picks up from whatever the most-recently-created user has, so
     * no change is needed there — only the existing seeded/live record needs
     * fixing up. Guarded so it's a no-op on a database that already has this,
     * has none, or already has something else occupying EMP-0000.
     */
    public function up(): void
    {
        $superAdmin = User::role('super_admin')->oldest('id')->first();

        if (! $superAdmin || $superAdmin->employee_code === 'EMP-0000') {
            return;
        }

        if (User::where('employee_code', 'EMP-0000')->exists()) {
            return;
        }

        $superAdmin->update(['employee_code' => 'EMP-0000']);
    }

    public function down(): void
    {
        // Data fix only — not reversible without knowing the admin's prior code.
    }
};
