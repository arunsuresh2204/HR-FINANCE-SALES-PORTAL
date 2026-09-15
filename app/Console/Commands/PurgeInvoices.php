<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeInvoices extends Command
{
    protected $signature = 'app:purge-invoices {--force : Skip the confirmation prompt}';

    protected $description = 'Deletes every invoice, payment, and billing request (and their tasks) for a clean slate. Leaves clients, leads, projects, and every other module untouched.';

    /**
     * Deleted in dependency order (children before parents) so this works
     * whether or not the connection has foreign key constraints enabled.
     */
    protected const TABLES_TO_CLEAR = [
        'payments',
        'invoices',
        'billing_request_tasks',
        'billing_requests',
    ];

    public function handle(): int
    {
        $counts = collect(self::TABLES_TO_CLEAR)
            ->filter(fn (string $table) => DB::getSchemaBuilder()->hasTable($table))
            ->mapWithKeys(fn (string $table) => [$table => DB::table($table)->count()]);

        if ($counts->sum() === 0) {
            $this->info('Nothing to delete — invoices, payments and billing requests are already empty.');

            return self::SUCCESS;
        }

        $this->warn('This will permanently delete:');
        foreach ($counts as $table => $count) {
            $this->line("  - {$count} row(s) from {$table}");
        }
        $this->line('Clients, leads, projects, and every other module are left untouched.');
        $this->line('Sales targets will show 0 achieved for past months until new payments are recorded.');

        if (! $this->option('force') && ! $this->confirm('Continue?')) {
            $this->info('Cancelled — nothing was changed.');

            return self::SUCCESS;
        }

        DB::transaction(function () {
            foreach (self::TABLES_TO_CLEAR as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->delete();
                }
            }
        });

        $this->info('All invoices, payments and billing requests have been deleted.');

        return self::SUCCESS;
    }
}
