<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * One-off dummy data for manual testing: a Manager -> Team Lead ->
 * Programmer engineering chain plus a Sales Exec with three clients/
 * projects exercising the three project-assignment paths. Not part of
 * the default `db:seed` run — invoke it explicitly:
 *   php artisan db:seed --class=DummyEngineeringTeamSeeder
 *
 * Safe to re-run: every record is keyed on something unique (email,
 * business_name, or client+name) via firstOrCreate, so running it twice
 * won't create duplicates or fail on the unique employee_code/email
 * constraints.
 */
class DummyEngineeringTeamSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $pradeep = User::firstOrCreate(
            ['email' => 'pradeep@nexstarc.com'],
            [
                'employee_code' => 'EMP-0012', 'name' => 'Pradeep', 'password' => $password,
                'email_verified_at' => now(), 'designation' => 'Manager - Engineering',
                'department' => 'Engineering', 'date_of_joining' => now()->subYear(), 'employment_status' => 'active',
                'monthly_salary' => 3500, 'scheduled_login_time' => '09:00', 'scheduled_logoff_time' => '18:00',
            ]
        );
        $pradeep->syncRoles(['manager_engineering']);

        $sajin = User::firstOrCreate(
            ['email' => 'sajin@nexstarc.com'],
            [
                'employee_code' => 'EMP-0013', 'name' => 'Sajin', 'password' => $password,
                'email_verified_at' => now(), 'designation' => 'Team Lead - Engineering',
                'department' => 'Engineering', 'date_of_joining' => now()->subMonths(8), 'employment_status' => 'active',
                'manager_id' => $pradeep->id,
                'monthly_salary' => 2600, 'scheduled_login_time' => '09:00', 'scheduled_logoff_time' => '18:00',
            ]
        );
        $sajin->update(['manager_id' => $pradeep->id]);
        $sajin->syncRoles(['team_lead_it', 'programmer']);

        // Raja Lakshmi reports to Sajin day-to-day, and to Pradeep as an
        // additional manager — she's visible under both in the hierarchy.
        $rajaLakshmi = User::firstOrCreate(
            ['email' => 'rajalakshmi@nexstarc.com'],
            [
                'employee_code' => 'EMP-0014', 'name' => 'Raja Lakshmi', 'password' => $password,
                'email_verified_at' => now(), 'designation' => 'Programmer',
                'department' => 'Engineering', 'date_of_joining' => now()->subMonths(4), 'employment_status' => 'active',
                'manager_id' => $sajin->id,
                'monthly_salary' => 2000, 'scheduled_login_time' => '09:00', 'scheduled_logoff_time' => '18:00',
            ]
        );
        $rajaLakshmi->update(['manager_id' => $sajin->id]);
        $rajaLakshmi->syncRoles(['programmer']);
        if (! $rajaLakshmi->additionalManagers()->wherePivot('manager_id', $pradeep->id)->exists()) {
            $rajaLakshmi->additionalManagers()->attach($pradeep->id);
        }

        $bibin = User::firstOrCreate(
            ['email' => 'bibin@nexstarc.com'],
            [
                'employee_code' => 'EMP-0015', 'name' => 'Bibin', 'password' => $password,
                'email_verified_at' => now(), 'designation' => 'Sales Executive',
                'department' => 'Sales', 'date_of_joining' => now()->subMonths(6), 'employment_status' => 'active',
                'monthly_salary' => 2000, 'scheduled_login_time' => '09:00', 'scheduled_logoff_time' => '18:00',
            ]
        );
        $bibin->syncRoles(['sales_exec']);

        // --- Clients (all Bibin's): one Indian, two European ---
        $indianClient = Client::firstOrCreate(
            ['business_name' => 'Anand Textiles Pvt Ltd'],
            [
                'sales_person_id' => $bibin->id, 'business_type' => 'Textiles & Apparel',
                'business_address' => '14 MG Road, Coimbatore, Tamil Nadu 641001, India',
                'owner_name' => 'Anand Krishnan', 'owner_designation' => 'Proprietor',
                'owner_contact' => 'anand@anandtextiles.in', 'owner_phone' => '+91 98765 43210',
                'tax_id' => '33AACFA1234B1Z5',
                'agreement_effective_date' => now()->subDays(20),
                'agreement_scope_summary' => 'Inventory & billing management system.',
            ]
        );

        $germanClient = Client::firstOrCreate(
            ['business_name' => 'Nordwerk GmbH'],
            [
                'sales_person_id' => $bibin->id, 'business_type' => 'Industrial Equipment',
                'business_address' => 'Friedrichstraße 45, 10117 Berlin, Germany',
                'owner_name' => 'Lukas Weber', 'owner_designation' => 'Managing Director',
                'owner_contact' => 'lukas.weber@nordwerk.de', 'owner_phone' => '+49 30 1234 5678',
                'tax_id' => 'DE123456789',
                'agreement_effective_date' => now()->subDays(15),
                'agreement_scope_summary' => 'Custom CRM for equipment leasing operations.',
            ]
        );

        $frenchClient = Client::firstOrCreate(
            ['business_name' => 'Atelier Rousseau SARL'],
            [
                'sales_person_id' => $bibin->id, 'business_type' => 'Interior Design',
                'business_address' => '22 Rue de Rivoli, 75004 Paris, France',
                'owner_name' => 'Camille Rousseau', 'owner_designation' => 'Founder',
                'owner_contact' => 'camille@atelierrousseau.fr', 'owner_phone' => '+33 1 42 60 30 30',
                'tax_id' => 'FR12345678901',
                'agreement_effective_date' => now()->subDays(10),
                'agreement_scope_summary' => 'Client portfolio showcase website with booking system.',
            ]
        );

        // --- Projects, one per client ---

        // 1. Assigned to Sajin alone.
        Project::firstOrCreate(
            ['client_id' => $indianClient->id, 'name' => 'Anand Textiles Inventory Portal'],
            [
                'created_by' => $bibin->id, 'assigned_to' => $sajin->id,
                'description' => 'Inventory and billing management portal.', 'status' => 'active', 'currency' => 'INR',
            ]
        );

        // 2. Assigned to Sajin, who staffs Raja Lakshmi on it as the developer.
        $project2 = Project::firstOrCreate(
            ['client_id' => $germanClient->id, 'name' => 'Nordwerk Leasing CRM'],
            [
                'created_by' => $bibin->id, 'assigned_to' => $sajin->id,
                'description' => 'Custom CRM for equipment leasing operations.', 'status' => 'active', 'currency' => 'EUR',
            ]
        );
        if (! $project2->developers()->where('user_id', $rajaLakshmi->id)->exists()) {
            $project2->developers()->attach($rajaLakshmi->id);
        }

        // 3. Assigned to Pradeep directly (the app only allows a manager,
        // team lead, or owner to hold the primary assignment), who staffs
        // Raja Lakshmi on it as the developer — bypassing Sajin entirely.
        $project3 = Project::firstOrCreate(
            ['client_id' => $frenchClient->id, 'name' => 'Atelier Rousseau Showcase Site'],
            [
                'created_by' => $bibin->id, 'assigned_to' => $pradeep->id,
                'description' => 'Client portfolio showcase website with booking system.', 'status' => 'active', 'currency' => 'EUR',
            ]
        );
        if (! $project3->developers()->where('user_id', $rajaLakshmi->id)->exists()) {
            $project3->developers()->attach($rajaLakshmi->id);
        }
    }
}
