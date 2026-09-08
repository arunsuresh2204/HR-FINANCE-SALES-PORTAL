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
use Illuminate\Support\Str;

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

        $sales3 = User::create([
            'employee_code' => 'EMP-0010', 'name' => 'Vishnu', 'email' => 'vishnu@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Sales Executive',
            'department' => 'Sales', 'date_of_joining' => now()->subMonths(2)->startOfMonth(), 'employment_status' => 'active',
            'monthly_salary' => 2000,
        ]);
        $sales3->assignRole('sales_exec');

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
        $employees = [$owner1, $owner2, $owner3, $owner4, $dev1, $dev2, $marketer1, $sales1, $sales2, $sales3];
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
            'invoice_number' => 'INV-2026-0001', 'currency' => 'USD', 'amount' => 4000, 'tax_percent' => 0, 'total_amount' => 4000,
            'amount_paid' => 4000, 'due_date' => now()->addDays(15), 'status' => 'paid',
        ]);

        BillingRequest::create([
            'client_id' => $client1->id, 'created_by' => $sales1->id, 'amount' => 4000,
            'milestone_description' => '50% on delivery', 'status' => 'pending',
        ]);

        // Vishnu's pipeline: 20 leads, 10 of which are won and converted to clients
        $vishnuLeads = [
            ['name' => 'Olivia Chen', 'company' => 'GreenLeaf Organics', 'country' => 'Australia', 'service' => 'web', 'source' => 'Referral', 'requirement' => 'Online store for organic skincare products', 'budget' => 6500, 'status' => 'won', 'type' => 'E-commerce / Skincare'],
            ['name' => 'Marcus Webb', 'company' => 'Webb & Sons Legal', 'country' => 'USA', 'service' => 'web', 'source' => 'LinkedIn', 'requirement' => 'Law firm website with client intake portal', 'budget' => 5200, 'status' => 'won', 'type' => 'Legal Services'],
            ['name' => 'Fatima Al-Sayed', 'company' => 'Sayed Interiors', 'country' => 'UAE', 'service' => 'web', 'source' => 'Instagram', 'requirement' => 'Portfolio site for interior design studio', 'budget' => 7800, 'status' => 'won', 'type' => 'Interior Design'],
            ['name' => "Liam O'Connor", 'company' => "O'Connor Fitness Studio", 'country' => 'Ireland', 'service' => 'mobile', 'source' => 'Referral', 'requirement' => 'Class booking app for boutique gym', 'budget' => 4300, 'status' => 'won', 'type' => 'Fitness'],
            ['name' => 'Priya Chandran', 'company' => 'SpiceRoute Foods', 'country' => 'India', 'service' => 'web', 'source' => 'Google Ads', 'requirement' => 'Online ordering site for spice exports', 'budget' => 3200, 'status' => 'won', 'type' => 'Food Exports'],
            ['name' => 'Hana Kobayashi', 'company' => 'Kobayashi Wellness Spa', 'country' => 'Japan', 'service' => 'mobile', 'source' => 'Upwork', 'requirement' => 'Appointment booking app for spa chain', 'budget' => 5600, 'status' => 'won', 'type' => 'Wellness'],
            ['name' => 'Diego Fernández', 'company' => 'Fernández Auto Parts', 'country' => 'Mexico', 'service' => 'other', 'source' => 'Referral', 'requirement' => 'Inventory management system', 'budget' => 4900, 'status' => 'won', 'type' => 'Automotive'],
            ['name' => 'Grace Mwangi', 'company' => 'Mwangi Handcrafts', 'country' => 'Kenya', 'service' => 'social_media', 'source' => 'Instagram', 'requirement' => 'Social media management for handcraft brand', 'budget' => 1800, 'status' => 'won', 'type' => 'Handcrafts'],
            ['name' => 'Tom Fletcher', 'company' => 'Fletcher Realty Group', 'country' => 'UK', 'service' => 'web', 'source' => 'LinkedIn', 'requirement' => 'Property listings website with search filters', 'budget' => 6100, 'status' => 'won', 'type' => 'Real Estate'],
            ['name' => 'Elena Petrova', 'company' => 'Petrova Beauty Bar', 'country' => 'Russia', 'service' => 'social_media', 'source' => 'Referral', 'requirement' => 'Social media management and content calendar', 'budget' => 2200, 'status' => 'won', 'type' => 'Beauty'],
            ['name' => 'Noah Bennett', 'company' => 'Bennett Bros. Construction', 'country' => 'USA', 'service' => 'web', 'source' => 'Google Ads', 'requirement' => 'Company website with project gallery', 'budget' => 9000, 'status' => 'new'],
            ['name' => 'Isabella Rossi', 'company' => 'Rossi Gourmet Deli', 'country' => 'Italy', 'service' => 'social_media', 'source' => 'Instagram', 'requirement' => 'Social media presence for new deli launch', 'budget' => 1500, 'status' => 'new'],
            ['name' => 'Kwame Asante', 'company' => 'Asante Tech Repairs', 'country' => 'Ghana', 'service' => 'mobile', 'source' => 'Referral', 'requirement' => 'Repair booking and tracking app', 'budget' => 2800, 'status' => 'contacted'],
            ['name' => 'Mei Lin', 'company' => 'Lin Family Dental', 'country' => 'Singapore', 'service' => 'web', 'source' => 'Google Ads', 'requirement' => 'Clinic website with appointment requests', 'budget' => 4700, 'status' => 'contacted'],
            ['name' => 'Jonas Berg', 'company' => 'Berg Outdoor Gear', 'country' => 'Sweden', 'service' => 'pet_product', 'source' => 'Upwork', 'requirement' => 'Product catalog for outdoor pet gear line', 'budget' => 3600, 'status' => 'proposal_sent'],
            ['name' => 'Aaliyah Brooks', 'company' => 'Brooks Pet Grooming', 'country' => 'USA', 'service' => 'pet_product', 'source' => 'Referral', 'requirement' => 'Booking site for mobile pet grooming service', 'budget' => 2100, 'status' => 'proposal_sent'],
            ['name' => 'Ravi Sharma', 'company' => 'Sharma Logistics', 'country' => 'India', 'service' => 'other', 'source' => 'LinkedIn', 'requirement' => 'Fleet tracking dashboard', 'budget' => 5400, 'status' => 'negotiation'],
            ['name' => 'Chloe Dubois', 'company' => 'Dubois Patisserie', 'country' => 'France', 'service' => 'social_media', 'source' => 'Instagram', 'requirement' => 'Social media management for patisserie chain', 'budget' => 1900, 'status' => 'negotiation'],
            ['name' => "Sam O'Neill", 'company' => "O'Neill Plumbing Services", 'country' => 'Canada', 'service' => 'web', 'source' => 'Google Ads', 'requirement' => 'Local service website with quote requests', 'budget' => 3300, 'status' => 'lost'],
            ['name' => 'Anika Patel', 'company' => 'Patel Yoga Studio', 'country' => 'India', 'service' => 'mobile', 'source' => 'Referral', 'requirement' => 'Class scheduling app for yoga studio', 'budget' => 2600, 'status' => 'lost'],
        ];

        foreach ($vishnuLeads as $i => $def) {
            $email = Str::slug($def['name'], '.').'@'.Str::slug($def['company'], '').'.com';

            $lead = Lead::create([
                'sales_person_id' => $sales3->id,
                'client_name' => $def['name'],
                'company_name' => $def['company'],
                'country' => $def['country'],
                'email' => $email,
                'phone' => '+1-555-03'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                'requirement' => $def['requirement'],
                'service_type' => $def['service'],
                'source' => $def['source'],
                'status' => $def['status'],
                'budget' => in_array($def['status'], ['won', 'lost']) ? $def['budget'] : null,
                'follow_up_date' => in_array($def['status'], ['won', 'lost']) ? null : now()->addDays(2 + ($i % 7)),
            ]);

            if ($def['status'] === 'won') {
                Client::create([
                    'lead_id' => $lead->id,
                    'sales_person_id' => $sales3->id,
                    'business_name' => $def['company'],
                    'business_type' => $def['type'] ?? null,
                    'business_address' => $def['country'],
                    'owner_name' => $def['name'],
                    'owner_designation' => 'Owner',
                    'owner_contact' => $email,
                    'agreement_effective_date' => now()->subDays($i + 1),
                    'agreement_scope_summary' => $def['requirement'],
                ]);
            }
        }

        // Sales targets (current month)
        SalesTarget::create(['user_id' => $sales1->id, 'month' => now()->month, 'year' => now()->year, 'target_amount' => 10000, 'commission_percent' => 5]);
        SalesTarget::create(['user_id' => $sales2->id, 'month' => now()->month, 'year' => now()->year, 'target_amount' => 8000, 'commission_percent' => 5]);
        SalesTarget::create(['user_id' => $sales3->id, 'month' => now()->month, 'year' => now()->year, 'target_amount' => 12000, 'commission_percent' => 5]);

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
