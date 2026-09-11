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
    protected $signature = 'app:clear-dummy-data
        {--keep=* : Email of an account to keep (repeatable). Defaults to every existing Super Admin account. Each kept account is set to hold only the Super Admin role, and is promoted to it if it doesn\'t already.}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Deletes every employee except the kept account(s) (stripped down to just Super Admin) and all business data (leads, clients, invoices, timesheets, attendance, etc). Resets core roles to their default permissions and removes any custom roles. Keeps holidays and lead sources.';

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
        $keepEmails = $this->option('keep');

        if (! empty($keepEmails)) {
            $keepUsers = User::whereIn('email', $keepEmails)->get();

            $missing = collect($keepEmails)->diff($keepUsers->pluck('email'));

            if ($missing->isNotEmpty()) {
                $this->error('No account found for: '.$missing->implode(', '));

                return self::FAILURE;
            }

            $keepIds = $keepUsers->pluck('id');
        } else {
            $keepIds = User::role('super_admin')->pluck('id');
            $keepUsers = User::whereIn('id', $keepIds)->get();
        }

        if ($keepIds->isEmpty()) {
            $this->error('No account to keep — aborting so you are not locked out. Pass --keep=you@example.com or make sure a Super Admin account exists.');

            return self::FAILURE;
        }

        $nonAdminCount = User::whereNotIn('id', $keepIds)->count();

        $this->warn('This will permanently delete:');
        $this->line("  - {$nonAdminCount} employee account(s)");
        $this->line('  - All leads, clients, invoices, timesheets, attendance, leave, sales targets, announcements, promotions, assets, expenses, projects, payrolls, policy documents, notifications, resignations, and employee/company documents');
        $this->line('  - Any custom functional role (e.g. Manager- Sales) and its Feature Access permissions');
        $this->line('Kept: '.$keepUsers->pluck('name')->implode(', ').' — reset to hold only the Super Admin role — plus the holiday calendar and lead source list.');

        if (! $this->option('force') && ! $this->confirm('Continue?')) {
            $this->info('Cancelled — nothing was changed.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($keepIds) {
            foreach (self::TABLES_TO_CLEAR as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->delete();
                }
            }

            $nonAdminIds = User::whereNotIn('id', $keepIds)->pluck('id');

            DB::table('model_has_roles')->whereIn('model_id', $nonAdminIds)->where('model_type', User::class)->delete();
            DB::table('model_has_permissions')->whereIn('model_id', $nonAdminIds)->where('model_type', User::class)->delete();

            // Clear reporting lines pointing at an account about to be
            // deleted, so a kept user never ends up with a dangling
            // manager_id (relevant when foreign key enforcement is off).
            User::whereIn('id', $keepIds)->whereNotIn('manager_id', $keepIds)->update(['manager_id' => null]);

            User::whereIn('id', $nonAdminIds)->delete();

            foreach (User::whereIn('id', $keepIds)->get() as $admin) {
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

        $this->info('Dummy data cleared. Kept '.$keepUsers->pluck('name')->implode(', ').', reset to hold only the Super Admin role. Core roles reset to their default Feature Access; custom roles removed. Holidays and lead sources were left untouched.');

        return self::SUCCESS;
    }
}
