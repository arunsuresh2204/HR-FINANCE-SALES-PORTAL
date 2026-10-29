<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RemoveDemoUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-demo-users {--keep=* : Additional email(s) to keep besides the Super Admin} {--force : Skip the confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete every user except the Super Admin (and any --keep emails). This cascades to everything those users created — projects, tasks, invoices, attendance, leave, etc.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $keep = collect($this->option('keep'))
            ->merge(User::role('super_admin')->pluck('email'))
            ->unique()
            ->values();

        if ($keep->isEmpty()) {
            $this->error('No Super Admin found and no --keep emails given — refusing to run, since that would delete every user.');

            return self::FAILURE;
        }

        $toDelete = User::whereNotIn('email', $keep)->get();

        if ($toDelete->isEmpty()) {
            $this->info('Nothing to delete — every user is already in the keep list.');

            return self::SUCCESS;
        }

        $this->warn('This will PERMANENTLY delete the following user(s), and everything that cascades from them — projects, tasks, invoices, attendance, leave requests, etc. that they created. This cannot be undone.');
        $this->table(
            ['Employee Code', 'Name', 'Email'],
            $toDelete->map(fn (User $u) => [$u->employee_code, $u->name, $u->email])->all()
        );
        $this->line('Keeping: '.$keep->implode(', '));

        if (! $this->option('force') && ! $this->confirm('Are you sure you want to permanently delete these '.$toDelete->count().' user(s)?')) {
            $this->info('Aborted — nothing was deleted.');

            return self::SUCCESS;
        }

        foreach ($toDelete as $user) {
            $user->syncRoles([]);
            $user->syncPermissions([]);
            $user->delete();
        }

        $this->info($toDelete->count().' user(s) deleted.');

        return self::SUCCESS;
    }
}
