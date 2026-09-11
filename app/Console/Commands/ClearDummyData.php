<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\FeatureCatalog;
use Database\Seeders\RoleSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ClearDummyData extends Command
{
    protected $signature = 'app:clear-dummy-data {--force : Skip the confirmation prompt}';

    protected $description = 'Deletes every employee except Super Admins (stripped down to just that role) and all business data (leads, clients, invoices, timesheets, attendance, etc). Resets core roles to their default permissions and removes any custom roles. Keeps holidays and lead sources.';

    /**
     * Deleted in dependency order (children before parents) so this works
     * whether or not the SQLite connection has foreign_key_constraints on.
     */
    protected const TABLES_TO_CLEAR = [
        'offboarding_tasks',
        'resignations',
        'employee_documents',
        'company_documents',
        'billing_request_tasks',
        'lead_activities',
        'leads',
        'timesheets',
        'marketing_logs',
        'attendance_status_requests',
        'attendances',
        'leave_requests',
        'sales_targets',
        'announcements',
        'promotions',
        'assets',
        'expenses',
        'project_developers',
        'invoices',
        'billing_requests',
        'projects',
        'clients',
        'payrolls',
        'policy_documents',
        'notifications',
        'user_additional_managers',
    ];

    public function handle(): int
    {
        $superAdminIds = User::role('super_admin')->pluck('id');

        if ($superAdminIds->isEmpty()) {
            $this->error('No Super Admin account found — aborting so you are not locked out.');

            return self::FAILURE;
        }

        $nonAdminCount = User::whereNotIn('id', $superAdminIds)->count();

        $this->warn('This will permanently delete:');
        $this->line("  - {$nonAdminCount} employee account(s) that are not Super Admin");
        $this->line('  - All leads, clients, invoices, timesheets, attendance, leave, sales targets, announcements, promotions, assets, expenses, projects, payrolls, policy documents, notifications, resignations, and employee/company documents');
        $this->line('  - Any custom functional role (e.g. Manager- Sales) and its Feature Access permissions');
        $this->line('Kept: Super Admin account(s) — reset to hold only the Super Admin role — plus the holiday calendar and lead source list.');

        if (! $this->option('force') && ! $this->confirm('Continue?')) {
            $this->info('Cancelled — nothing was changed.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($superAdminIds) {
            foreach (self::TABLES_TO_CLEAR as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->delete();
                }
            }

            $nonAdminIds = User::whereNotIn('id', $superAdminIds)->pluck('id');

            DB::table('model_has_roles')->whereIn('model_id', $nonAdminIds)->where('model_type', User::class)->delete();
            DB::table('model_has_permissions')->whereIn('model_id', $nonAdminIds)->where('model_type', User::class)->delete();
            User::whereIn('id', $nonAdminIds)->delete();

            foreach (User::whereIn('id', $superAdminIds)->get() as $admin) {
                $admin->syncRoles(['super_admin']);
            }

            foreach (FeatureCatalog::DEFAULT_ROLE_PERMISSIONS as $roleName => $permissions) {
                $role = Role::where('name', $roleName)->first();

                if ($role) {
                    $role->syncPermissions($permissions);
                }
            }

            Role::whereNotIn('name', RoleSeeder::ROLES)->get()->each(function (Role $role) {
                $role->users()->detach();
                $role->syncPermissions([]);
                $role->delete();
            });
        });

        $this->info('Dummy data cleared. Super Admin account(s) kept, reset to the Super Admin role only. Core roles reset to their default Feature Access; custom roles removed. Holidays and lead sources were left untouched.');

        return self::SUCCESS;
    }
}
