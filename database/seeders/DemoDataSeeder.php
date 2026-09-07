<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Asset;
use App\Models\Attendance;
use App\Models\BillingRequest;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\MarketingLog;
use App\Models\PolicyDocument;
use App\Models\SalesTarget;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $owner1 = User::create([
            'employee_code' => 'EMP-0001', 'name' => 'Arun Suresh', 'email' => 'arun@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Co-Founder / Lead Developer',
            'department' => 'Engineering', 'date_of_joining' => '2021-01-01', 'employment_status' => 'active',
            'monthly_salary' => 4000,
        ]);
        $owner1->assignRole(['super_admin', 'programmer']);

        $owner2 = User::create([
            'employee_code' => 'EMP-0002', 'name' => 'Rahul Menon', 'email' => 'rahul@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Co-Founder / Developer',
            'department' => 'Engineering', 'date_of_joining' => '2021-01-01', 'employment_status' => 'active',
            'monthly_salary' => 4000,
        ]);
        $owner2->assignRole(['super_admin', 'programmer']);

        $owner3 = User::create([
            'employee_code' => 'EMP-0003', 'name' => 'Priya Nair', 'email' => 'priya@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Co-Founder / Sales & Finance',
            'department' => 'Sales', 'date_of_joining' => '2021-01-01', 'employment_status' => 'active',
            'monthly_salary' => 4500,
        ]);
        $owner3->assignRole(['super_admin', 'sales_exec', 'finance_admin']);

        $owner4 = User::create([
            'employee_code' => 'EMP-0004', 'name' => 'Karthik Iyer', 'email' => 'karthik@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Co-Founder / Sales & HR',
            'department' => 'Sales', 'date_of_joining' => '2021-01-01', 'employment_status' => 'active',
            'monthly_salary' => 4500,
        ]);
        $owner4->assignRole(['super_admin', 'sales_exec', 'hr_admin']);

        $dev1 = User::create([
            'employee_code' => 'EMP-0005', 'name' => 'Sneha Reddy', 'email' => 'sneha@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Full Stack Developer',
            'department' => 'Engineering', 'date_of_joining' => '2022-03-15', 'employment_status' => 'active',
            'monthly_salary' => 2200,
        ]);
        $dev1->assignRole('programmer');

        $dev2 = User::create([
            'employee_code' => 'EMP-0006', 'name' => 'Vikram Shah', 'email' => 'vikram@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Mobile App Developer',
            'department' => 'Engineering', 'date_of_joining' => '2022-06-01', 'employment_status' => 'active',
            'monthly_salary' => 2200,
        ]);
        $dev2->assignRole('programmer');

        $marketer1 = User::create([
            'employee_code' => 'EMP-0007', 'name' => 'Anjali Verma', 'email' => 'anjali@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Social Media Marketer',
            'department' => 'Marketing', 'date_of_joining' => '2022-09-01', 'employment_status' => 'active',
            'monthly_salary' => 1800,
        ]);
        $marketer1->assignRole('marketer');

        $sales1 = User::create([
            'employee_code' => 'EMP-0008', 'name' => 'Rohan Kapoor', 'email' => 'rohan@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Sales Executive',
            'department' => 'Sales', 'date_of_joining' => '2023-01-10', 'employment_status' => 'active',
            'monthly_salary' => 2000,
        ]);
        $sales1->assignRole('sales_exec');

        $sales2 = User::create([
            'employee_code' => 'EMP-0009', 'name' => 'Divya Pillai', 'email' => 'divya@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Sales Executive',
            'department' => 'Sales', 'date_of_joining' => '2023-04-20', 'employment_status' => 'active',
            'monthly_salary' => 2000,
        ]);
        $sales2->assignRole('sales_exec');

        // Announcements
        Announcement::create([
            'posted_by' => $owner1->id, 'title' => 'Welcome to the Nexstarc Portal',
            'body' => 'This is our new unified HR, Finance & Sales portal. Please update your profile and explore your dashboard.',
            'pinned' => true,
        ]);
        Announcement::create([
            'posted_by' => $owner4->id, 'title' => 'Q3 All-Hands Meeting',
            'body' => 'All-hands meeting scheduled for the last Friday of this quarter. Attendance mandatory.',
        ]);

        // Policy documents
        PolicyDocument::create([
            'uploaded_by' => $owner4->id, 'title' => 'Employee Handbook',
            'description' => 'General conduct, working hours, and company policies.', 'file_path' => 'policies/employee-handbook.pdf',
        ]);
        PolicyDocument::create([
            'uploaded_by' => $owner4->id, 'title' => 'Leave Policy',
            'description' => 'Annual leave entitlement and approval process.', 'file_path' => 'policies/leave-policy.pdf',
        ]);

        // Attendance (last 5 working days) for all employees
        $employees = [$owner1, $owner2, $owner3, $owner4, $dev1, $dev2, $marketer1, $sales1, $sales2];
        foreach ($employees as $emp) {
            for ($i = 1; $i <= 5; $i++) {
                $date = now()->subDays($i);
                if ($date->isWeekend()) {
                    continue;
                }
                Attendance::create([
                    'user_id' => $emp->id,
                    'work_date' => $date->toDateString(),
                    'clock_in' => $date->copy()->setTime(9, random_int(0, 20)),
                    'clock_out' => $date->copy()->setTime(18, random_int(0, 30)),
                    'status' => 'present',
                ]);
            }
        }

        // Leave requests
        LeaveRequest::create([
            'user_id' => $dev1->id, 'type' => 'vacation', 'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(12), 'days' => 3, 'reason' => 'Family trip', 'status' => 'pending',
        ]);
        LeaveRequest::create([
            'user_id' => $marketer1->id, 'type' => 'sick', 'start_date' => now()->subDays(3),
            'end_date' => now()->subDays(2), 'days' => 2, 'reason' => 'Fever', 'status' => 'approved',
            'reviewed_by' => $owner4->id, 'reviewed_at' => now()->subDays(3),
        ]);

        // Timesheets
        Timesheet::create([
            'user_id' => $dev1->id, 'project_name' => 'In-house Pet Product', 'work_date' => now()->subDay(),
            'task_description' => 'Implemented booking API', 'hours' => 6.5, 'status' => 'completed',
        ]);
        Timesheet::create([
            'user_id' => $dev2->id, 'project_name' => 'Mobile App v2', 'work_date' => now()->subDay(),
            'task_description' => 'Fixed push notification bug', 'hours' => 5, 'status' => 'completed',
        ]);

        // Marketing logs
        MarketingLog::create([
            'user_id' => $marketer1->id, 'work_date' => now()->subDay(), 'platform' => 'Instagram',
            'task_type' => 'content', 'is_in_house_product' => true, 'hours' => 4,
            'notes' => 'Created 3 reels for pet product launch',
        ]);

        // Leads
        $lead1 = Lead::create([
            'sales_person_id' => $sales1->id, 'client_name' => 'Michael Torres', 'company_name' => 'PawCare Co.',
            'country' => 'USA', 'email' => 'michael@pawcare.com', 'phone' => '+1-555-0101',
            'requirement' => 'E-commerce website for pet supplies', 'service_type' => 'web',
            'source' => 'Upwork', 'status' => 'won', 'budget' => 8000, 'follow_up_date' => now()->addDays(2),
        ]);
        Lead::create([
            'sales_person_id' => $sales2->id, 'client_name' => 'Sarah Lin', 'company_name' => 'BrightBrand Media',
            'country' => 'Canada', 'email' => 'sarah@brightbrand.com', 'phone' => '+1-555-0199',
            'requirement' => 'Social media management retainer', 'service_type' => 'social_media',
            'source' => 'LinkedIn', 'status' => 'proposal_sent', 'budget' => 1500, 'follow_up_date' => now()->addDays(3),
        ]);
        Lead::create([
            'sales_person_id' => $owner4->id, 'client_name' => 'James Okafor', 'company_name' => null,
            'country' => 'UK', 'email' => 'james@example.com', 'phone' => '+44-7700-900123',
            'requirement' => 'Mobile app for local delivery service', 'service_type' => 'mobile',
            'source' => 'Referral', 'status' => 'new', 'budget' => null,
        ]);

        // Client conversion for lead1
        $client1 = Client::create([
            'lead_id' => $lead1->id, 'sales_person_id' => $sales1->id, 'business_name' => 'PawCare Co.',
            'business_type' => 'E-commerce / Pet Industry', 'business_address' => '123 Bark Ave, Austin, TX',
            'owner_name' => 'Michael Torres', 'owner_designation' => 'Founder', 'owner_contact' => 'michael@pawcare.com',
            'agreement_effective_date' => now()->subDays(5), 'agreement_scope_summary' => 'Full e-commerce build, 3 month engagement',
        ]);

        $billing1 = BillingRequest::create([
            'client_id' => $client1->id, 'created_by' => $sales1->id, 'amount' => 4000,
            'milestone_description' => '50% advance', 'status' => 'invoiced',
        ]);

        Invoice::create([
            'billing_request_id' => $billing1->id, 'client_id' => $client1->id, 'created_by' => $owner3->id,
            'invoice_number' => 'INV-2026-0001', 'amount' => 4000, 'tax_percent' => 0, 'total_amount' => 4000,
            'amount_paid' => 4000, 'due_date' => now()->addDays(15), 'status' => 'paid',
        ]);

        BillingRequest::create([
            'client_id' => $client1->id, 'created_by' => $sales1->id, 'amount' => 4000,
            'milestone_description' => '50% on delivery', 'status' => 'pending',
        ]);

        // Sales targets (current month)
        SalesTarget::create(['user_id' => $sales1->id, 'month' => now()->month, 'year' => now()->year, 'target_amount' => 10000, 'commission_percent' => 5]);
        SalesTarget::create(['user_id' => $sales2->id, 'month' => now()->month, 'year' => now()->year, 'target_amount' => 8000, 'commission_percent' => 5]);

        // Expenses
        Expense::create([
            'user_id' => $marketer1->id, 'amount' => 45.99, 'category' => 'software',
            'description' => 'Canva Pro subscription', 'expense_date' => now()->subDays(4), 'status' => 'pending',
        ]);
        Expense::create([
            'user_id' => $dev2->id, 'amount' => 120, 'category' => 'travel',
            'description' => 'Client visit taxi fare', 'expense_date' => now()->subDays(6),
            'status' => 'approved', 'reviewed_by' => $owner3->id, 'reviewed_at' => now()->subDays(5),
        ]);

        // Assets
        Asset::create(['user_id' => $dev1->id, 'item_name' => 'MacBook Pro 14"', 'item_type' => 'Laptop', 'assigned_date' => '2022-03-15']);
        Asset::create(['user_id' => $marketer1->id, 'item_name' => 'Adobe Creative Cloud License', 'item_type' => 'Software', 'assigned_date' => '2022-09-01']);
    }
}
