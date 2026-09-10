<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Asset;
use App\Models\Attendance;
use App\Models\AttendanceStatusRequest;
use App\Models\BillingRequest;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeaveRequest;
use App\Models\MarketingLog;
use App\Models\PolicyDocument;
use App\Models\Project;
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
            'monthly_salary' => 4000, 'scheduled_login_time' => '09:00', 'scheduled_logoff_time' => '18:00',
        ]);
        $owner1->assignRole(['super_admin', 'programmer']);

        $owner2 = User::create([
            'employee_code' => 'EMP-0002', 'name' => 'Rahul Menon', 'email' => 'rahul@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Co-Founder / Developer',
            'department' => 'Engineering', 'date_of_joining' => '2021-01-01', 'employment_status' => 'active',
            'monthly_salary' => 4000, 'scheduled_login_time' => '09:00', 'scheduled_logoff_time' => '18:00',
        ]);
        $owner2->assignRole(['super_admin', 'programmer']);

        $owner3 = User::create([
            'employee_code' => 'EMP-0003', 'name' => 'Priya Nair', 'email' => 'priya@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Co-Founder / Sales & Finance',
            'department' => 'Sales', 'date_of_joining' => '2021-01-01', 'employment_status' => 'active',
            'monthly_salary' => 4500, 'scheduled_login_time' => '09:30', 'scheduled_logoff_time' => '18:30',
        ]);
        $owner3->assignRole(['super_admin', 'sales_exec', 'finance_admin', 'manager']);

        $owner4 = User::create([
            'employee_code' => 'EMP-0004', 'name' => 'Karthik Iyer', 'email' => 'karthik@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Co-Founder / Sales & HR',
            'department' => 'Sales', 'date_of_joining' => '2021-01-01', 'employment_status' => 'active',
            'monthly_salary' => 4500, 'scheduled_login_time' => '09:30', 'scheduled_logoff_time' => '18:30',
        ]);
        $owner4->assignRole(['super_admin', 'sales_exec', 'hr_admin', 'manager']);

        $dev1 = User::create([
            'employee_code' => 'EMP-0005', 'name' => 'Sneha Reddy', 'email' => 'sneha@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Full Stack Developer',
            'department' => 'Engineering', 'date_of_joining' => '2022-03-15', 'employment_status' => 'active',
            'monthly_salary' => 2200, 'manager_id' => $owner1->id,
            'scheduled_login_time' => '09:00', 'scheduled_logoff_time' => '18:00',
        ]);
        $dev1->assignRole('programmer');

        $dev2 = User::create([
            'employee_code' => 'EMP-0006', 'name' => 'Vikram Shah', 'email' => 'vikram@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Mobile App Developer',
            'department' => 'Engineering', 'date_of_joining' => '2022-06-01', 'employment_status' => 'active',
            'monthly_salary' => 2200, 'manager_id' => $owner1->id,
            'scheduled_login_time' => '10:00', 'scheduled_logoff_time' => '19:00',
        ]);
        $dev2->assignRole('programmer');

        $marketer1 = User::create([
            'employee_code' => 'EMP-0007', 'name' => 'Anjali Verma', 'email' => 'anjali@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Social Media Marketer',
            'department' => 'Marketing', 'date_of_joining' => '2022-09-01', 'employment_status' => 'active',
            'monthly_salary' => 1800, 'manager_id' => $owner3->id,
            'scheduled_login_time' => '10:00', 'scheduled_logoff_time' => '19:00',
        ]);
        $marketer1->assignRole('marketer');

        $sales1 = User::create([
            'employee_code' => 'EMP-0008', 'name' => 'Rohan Kapoor', 'email' => 'rohan@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Sales Team Lead',
            'department' => 'Sales', 'date_of_joining' => '2023-01-10', 'employment_status' => 'active',
            'monthly_salary' => 2000, 'manager_id' => $owner4->id,
            'scheduled_login_time' => '09:00', 'scheduled_logoff_time' => '18:00',
        ]);
        $sales1->assignRole(['sales_exec', 'team_lead']);

        $sales2 = User::create([
            'employee_code' => 'EMP-0009', 'name' => 'Divya Pillai', 'email' => 'divya@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Sales Executive',
            'department' => 'Sales', 'date_of_joining' => '2023-04-20', 'employment_status' => 'active',
            'monthly_salary' => 2000, 'manager_id' => $sales1->id,
            'scheduled_login_time' => '09:00', 'scheduled_logoff_time' => '18:00',
        ]);
        $sales2->assignRole('sales_exec');

        $sales3 = User::create([
            'employee_code' => 'EMP-0010', 'name' => 'Vishnu', 'email' => 'vishnu@nexstarc.com',
            'password' => $password, 'email_verified_at' => now(), 'designation' => 'Sales Executive',
            'department' => 'Sales', 'date_of_joining' => now()->subMonths(2)->startOfMonth(), 'employment_status' => 'active',
            'monthly_salary' => 2000, 'manager_id' => $sales1->id,
            'scheduled_login_time' => '09:30', 'scheduled_logoff_time' => '18:30',
        ]);
        $sales3->assignRole('sales_exec');
        $sales3->additionalManagers()->attach($owner3->id);

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

        // Attendance (last 5 working days) for all employees, clocking in around their scheduled time
        $employees = [$owner1, $owner2, $owner3, $owner4, $dev1, $dev2, $marketer1, $sales1, $sales2, $sales3];
        foreach ($employees as $emp) {
            [$schedHour, $schedMinute] = array_map('intval', explode(':', $emp->scheduled_login_time));

            for ($i = 1; $i <= 5; $i++) {
                $date = now()->subDays($i);
                if ($date->isWeekend()) {
                    continue;
                }

                $scheduledAt = $date->copy()->setTime($schedHour, $schedMinute);
                $clockIn = $scheduledAt->copy()->addMinutes(random_int(-10, 15));

                Attendance::create([
                    'user_id' => $emp->id,
                    'work_date' => $date->toDateString(),
                    'scheduled_login_time' => $emp->scheduled_login_time,
                    'clock_in' => $clockIn,
                    'clock_out' => $date->copy()->setTime(18, random_int(0, 30)),
                    'status' => $clockIn->lte($scheduledAt) ? 'present' : 'late',
                ]);
            }
        }

        // A few explicit examples so every late-tier and the leave override show up in the Attendance Oversight list
        $twoDaysAgo = now()->subDays(2);
        Attendance::where('user_id', $sales2->id)->whereDate('work_date', $twoDaysAgo->toDateString())->delete();
        $sales2GraceScheduledAt = $twoDaysAgo->copy()->setTime(9, 0);
        $sales2AttendanceGrace = Attendance::create([
            'user_id' => $sales2->id,
            'work_date' => $twoDaysAgo->toDateString(),
            'scheduled_login_time' => $sales2->scheduled_login_time,
            'clock_in' => $sales2GraceScheduledAt->copy()->addMinutes(18),
            'clock_out' => $twoDaysAgo->copy()->setTime(18, 5),
            'status' => 'late',
        ]);

        Attendance::where('user_id', $dev2->id)->whereDate('work_date', $twoDaysAgo->toDateString())->delete();
        $dev2SevereScheduledAt = $twoDaysAgo->copy()->setTime(10, 0);
        Attendance::create([
            'user_id' => $dev2->id,
            'work_date' => $twoDaysAgo->toDateString(),
            'scheduled_login_time' => $dev2->scheduled_login_time,
            'clock_in' => $dev2SevereScheduledAt->copy()->addMinutes(55),
            'clock_out' => $twoDaysAgo->copy()->setTime(19, 10),
            'status' => 'late',
        ]);

        // Vishnu never clocked in two days ago and has no leave on file for it — auto-mark absent
        Attendance::where('user_id', $sales3->id)->whereDate('work_date', $twoDaysAgo->toDateString())->delete();
        $sales3AbsentAttendance = Attendance::create([
            'user_id' => $sales3->id,
            'work_date' => $twoDaysAgo->toDateString(),
            'scheduled_login_time' => $sales3->scheduled_login_time,
            'status' => 'absent',
        ]);

        AttendanceStatusRequest::create([
            'attendance_id' => $sales3AbsentAttendance->id,
            'user_id' => $sales3->id,
            'requested_status' => 'present',
            'reason' => "Had a power outage at home and my phone was dead — I called Rohan but couldn't reach the portal to clock in. I was working from a café by 11am.",
            'status' => 'pending',
        ]);

        AttendanceStatusRequest::create([
            'attendance_id' => $sales2AttendanceGrace->id,
            'user_id' => $sales2->id,
            'requested_status' => 'present',
            'reason' => 'Traffic due to road work near my place, only a few minutes late.',
            'status' => 'rejected',
            'reviewed_by' => $owner4->id,
            'reviewed_at' => now()->subDay(),
            'review_notes' => 'Understood, but still outside the grace window — keeping as late this time.',
        ]);

        // Sneha Reddy: 30-day attendance history with a full variety of scenarios,
        // replacing the generic 5-day rows the loop above gave her.
        Attendance::where('user_id', $dev1->id)->delete();

        LeaveRequest::create([
            'user_id' => $dev1->id, 'type' => 'vacation', 'start_date' => now()->subDays(15),
            'end_date' => now()->subDays(13), 'days' => 3, 'reason' => 'Family wedding out of town', 'status' => 'approved',
            'reviewed_by' => $owner1->id, 'reviewed_at' => now()->subDays(16),
        ]);
        LeaveRequest::create([
            'user_id' => $dev1->id, 'type' => 'sick', 'start_date' => now()->subDays(21),
            'end_date' => now()->subDays(21), 'days' => 1, 'reason' => 'Migraine', 'status' => 'approved',
            'reviewed_by' => $owner1->id, 'reviewed_at' => now()->subDays(21),
        ]);

        $snehaScenarios = [
            1 => ['type' => 'on_time'],
            2 => ['type' => 'severe', 'minutes' => 55, 'dispute' => 'rejected'],
            3 => ['type' => 'grace', 'minutes' => 12],
            6 => ['type' => 'absent'],
            7 => ['type' => 'no_clock_out'],
            8 => ['type' => 'on_time'],
            9 => ['type' => 'grace', 'minutes' => 22],
            10 => ['type' => 'on_time'],
            16 => ['type' => 'early_out'],
            17 => ['type' => 'severe', 'minutes' => 40],
            20 => ['type' => 'grace', 'minutes' => 8],
            22 => ['type' => 'on_time'],
            23 => ['type' => 'on_time'],
            24 => ['type' => 'absent', 'dispute' => 'pending'],
            27 => ['type' => 'grace', 'minutes' => 27],
            28 => ['type' => 'on_time'],
            29 => ['type' => 'on_time'],
            30 => ['type' => 'on_time'],
        ];
        // Offsets 13-15 and 21 are covered by the leave requests above and intentionally skipped here.

        foreach ($snehaScenarios as $offset => $scenario) {
            $date = now()->subDays($offset);
            $scheduledAt = $date->copy()->setTime(9, 0);

            if ($scenario['type'] === 'absent') {
                $attendance = Attendance::create([
                    'user_id' => $dev1->id,
                    'work_date' => $date->toDateString(),
                    'scheduled_login_time' => '09:00',
                    'status' => 'absent',
                ]);

                if (($scenario['dispute'] ?? null) === 'pending') {
                    AttendanceStatusRequest::create([
                        'attendance_id' => $attendance->id,
                        'user_id' => $dev1->id,
                        'requested_status' => 'present',
                        'reason' => 'Our office VPN was down all morning (IT ticket #4471) — I was working from my personal laptop but couldn\'t reach the portal to clock in.',
                        'status' => 'pending',
                    ]);
                }

                continue;
            }

            $clockIn = match ($scenario['type']) {
                'grace', 'severe' => $scheduledAt->copy()->addMinutes($scenario['minutes']),
                default => $scheduledAt->copy()->addMinutes(random_int(-10, 0)),
            };
            $clockOut = match ($scenario['type']) {
                'early_out' => $date->copy()->setTime(14, 0),
                default => $date->copy()->setTime(18, random_int(0, 20)),
            };

            $attendance = Attendance::create([
                'user_id' => $dev1->id,
                'work_date' => $date->toDateString(),
                'scheduled_login_time' => '09:00',
                'clock_in' => $clockIn,
                'clock_out' => $scenario['type'] === 'no_clock_out' ? null : $clockOut,
                'status' => $clockIn->lte($scheduledAt) ? 'present' : 'late',
            ]);

            if (($scenario['dispute'] ?? null) === 'rejected') {
                AttendanceStatusRequest::create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $dev1->id,
                    'requested_status' => 'present',
                    'reason' => 'Train delay on my commute — only a little past the window.',
                    'status' => 'rejected',
                    'reviewed_by' => $owner1->id,
                    'reviewed_at' => $date->copy()->addDay(),
                    'review_notes' => 'Understood, but this was well past the grace period — keeping as late.',
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
            'requirement' => 'E-commerce website for pet supplies', 'service_type' => 'Web Development',
            'source' => 'Upwork', 'status' => 'won', 'budget' => 8000, 'follow_up_date' => now()->addDays(2),
            'contacted_date' => now()->subDays(10),
        ]);
        Lead::create([
            'sales_person_id' => $sales2->id, 'client_name' => 'Sarah Lin', 'company_name' => 'BrightBrand Media',
            'country' => 'Canada', 'email' => 'sarah@brightbrand.com', 'phone' => '+1-555-0199',
            'requirement' => 'Social media management retainer', 'service_type' => 'Social Media',
            'source' => 'LinkedIn', 'status' => 'proposal_sent', 'budget' => 1500, 'follow_up_date' => now()->addDays(3),
            'contacted_date' => now()->subDays(4),
        ]);
        Lead::create([
            'sales_person_id' => $owner4->id, 'client_name' => 'James Okafor', 'company_name' => null,
            'country' => 'UK', 'email' => 'james@example.com', 'phone' => '+44-7700-900123',
            'requirement' => 'Mobile app for local delivery service', 'service_type' => 'Mobile App',
            'source' => 'Referral', 'status' => 'pending', 'budget' => null,
            'contacted_date' => now()->subDay(),
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

        // Projects for PawCare Co.: one assigned to a manager, one assigned to an owner, each staffed with developers
        $project1 = Project::create([
            'client_id' => $client1->id, 'created_by' => $sales1->id, 'assigned_to' => $owner4->id,
            'name' => 'PawCare Storefront Build', 'description' => 'Full e-commerce site for pet supplies.', 'status' => 'active',
        ]);
        $project1->developers()->attach([$dev1->id, $dev2->id]);

        $project2 = Project::create([
            'client_id' => $client1->id, 'created_by' => $owner1->id, 'assigned_to' => $owner1->id,
            'name' => 'PawCare Mobile Companion App', 'description' => 'Follow-on mobile app once the storefront ships.', 'status' => 'on_hold',
        ]);
        $project2->developers()->attach([$dev2->id]);

        // Vishnu's pipeline: 20 leads, 10 of which are won and converted to clients
        $vishnuLeads = [
            ['name' => 'Olivia Chen', 'company' => 'GreenLeaf Organics', 'country' => 'Australia', 'service' => 'Web Development', 'source' => 'Referral', 'requirement' => 'Online store for organic skincare products', 'budget' => 6500, 'status' => 'won', 'type' => 'E-commerce / Skincare'],
            ['name' => 'Marcus Webb', 'company' => 'Webb & Sons Legal', 'country' => 'USA', 'service' => 'Web Development', 'source' => 'LinkedIn', 'requirement' => 'Law firm website with client intake portal', 'budget' => 5200, 'status' => 'won', 'type' => 'Legal Services'],
            ['name' => 'Fatima Al-Sayed', 'company' => 'Sayed Interiors', 'country' => 'UAE', 'service' => 'WordPress', 'source' => 'Instagram', 'requirement' => 'Portfolio site for interior design studio', 'budget' => 7800, 'status' => 'won', 'type' => 'Interior Design'],
            ['name' => "Liam O'Connor", 'company' => "O'Connor Fitness Studio", 'country' => 'Ireland', 'service' => 'Mobile App', 'source' => 'Referral', 'requirement' => 'Class booking app for boutique gym', 'budget' => 4300, 'status' => 'won', 'type' => 'Fitness'],
            ['name' => 'Priya Chandran', 'company' => 'SpiceRoute Foods', 'country' => 'India', 'service' => 'E-commerce', 'source' => 'Google Ads', 'requirement' => 'Online ordering site for spice exports', 'budget' => 3200, 'status' => 'won', 'type' => 'Food Exports'],
            ['name' => 'Hana Kobayashi', 'company' => 'Kobayashi Wellness Spa', 'country' => 'Japan', 'service' => 'Mobile App', 'source' => 'Upwork', 'requirement' => 'Appointment booking app for spa chain', 'budget' => 5600, 'status' => 'won', 'type' => 'Wellness'],
            ['name' => 'Diego Fernández', 'company' => 'Fernández Auto Parts', 'country' => 'Mexico', 'service' => 'Other', 'source' => 'Referral', 'requirement' => 'Inventory management system', 'budget' => 4900, 'status' => 'won', 'type' => 'Automotive'],
            ['name' => 'Grace Mwangi', 'company' => 'Mwangi Handcrafts', 'country' => 'Kenya', 'service' => 'Digital Marketing', 'source' => 'Instagram', 'requirement' => 'Social media management for handcraft brand', 'budget' => 1800, 'status' => 'won', 'type' => 'Handcrafts'],
            ['name' => 'Tom Fletcher', 'company' => 'Fletcher Realty Group', 'country' => 'UK', 'service' => 'Web Development', 'source' => 'LinkedIn', 'requirement' => 'Property listings website with search filters', 'budget' => 6100, 'status' => 'won', 'type' => 'Real Estate'],
            ['name' => 'Elena Petrova', 'company' => 'Petrova Beauty Bar', 'country' => 'Russia', 'service' => 'Digital Marketing', 'source' => 'Referral', 'requirement' => 'Social media management and content calendar', 'budget' => 2200, 'status' => 'won', 'type' => 'Beauty'],
            ['name' => 'Noah Bennett', 'company' => 'Bennett Bros. Construction', 'country' => 'USA', 'service' => 'Web Development', 'source' => 'Google Ads', 'requirement' => 'Company website with project gallery', 'budget' => 9000, 'status' => 'pending'],
            ['name' => 'Isabella Rossi', 'company' => 'Rossi Gourmet Deli', 'country' => 'Italy', 'service' => 'Digital Marketing', 'source' => 'Instagram', 'requirement' => 'Social media presence for new deli launch', 'budget' => 1500, 'status' => 'pending'],
            ['name' => 'Kwame Asante', 'company' => 'Asante Tech Repairs', 'country' => 'Ghana', 'service' => 'Mobile App', 'source' => 'Referral', 'requirement' => 'Repair booking and tracking app', 'budget' => 2800, 'status' => 'positive', 'comment' => 'Replied enthusiastically, wants a call this week.'],
            ['name' => 'Mei Lin', 'company' => 'Lin Family Dental', 'country' => 'Singapore', 'service' => 'WordPress', 'source' => 'Google Ads', 'requirement' => 'Clinic website with appointment requests', 'budget' => 4700, 'status' => 'negative', 'comment' => 'Said budget is too tight right now, revisit in Q4.'],
            ['name' => 'Jonas Berg', 'company' => 'Berg Outdoor Gear', 'country' => 'Sweden', 'service' => 'E-commerce', 'source' => 'Upwork', 'requirement' => 'Product catalog for outdoor pet gear line', 'budget' => 3600, 'status' => 'proposal_sent', 'comment' => 'Proposal sent, awaiting sign-off from their ops team.', 'contact_link' => 'https://www.linkedin.com/in/jonasberg-example'],
            ['name' => 'Aaliyah Brooks', 'company' => 'Brooks Pet Grooming', 'country' => 'USA', 'service' => 'Mobile App', 'source' => 'Referral', 'requirement' => 'Booking site for mobile pet grooming service', 'budget' => 2100, 'status' => 'proposal_sent'],
            ['name' => 'Ravi Sharma', 'company' => 'Sharma Logistics', 'country' => 'India', 'service' => 'Other', 'source' => 'LinkedIn', 'requirement' => 'Fleet tracking dashboard', 'budget' => 5400, 'status' => 'proposal_sent', 'contact_link' => 'https://www.linkedin.com/in/ravisharma-example'],
            ['name' => 'Chloe Dubois', 'company' => 'Dubois Patisserie', 'country' => 'France', 'service' => 'Digital Marketing', 'source' => 'Instagram', 'requirement' => 'Social media management for patisserie chain', 'budget' => 1900, 'status' => 'rejected', 'comment' => 'Went with a local agency instead.'],
            ['name' => "Sam O'Neill", 'company' => "O'Neill Plumbing Services", 'country' => 'Canada', 'service' => 'Web Development', 'source' => 'Google Ads', 'requirement' => 'Local service website with quote requests', 'budget' => 3300, 'status' => 'lost'],
            ['name' => 'Anika Patel', 'company' => 'Patel Yoga Studio', 'country' => 'India', 'service' => 'Mobile App', 'source' => 'Referral', 'requirement' => 'Class scheduling app for yoga studio', 'budget' => 2600, 'status' => 'lost'],
        ];

        foreach ($vishnuLeads as $i => $def) {
            $email = Str::slug($def['name'], '.').'@'.Str::slug($def['company'], '').'.com';
            $isClosed = in_array($def['status'], \App\Models\Lead::CLOSED_STATUSES, true);

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
                'budget' => $isClosed ? $def['budget'] : null,
                'follow_up_date' => $isClosed ? null : now()->addDays(2 + ($i % 7)),
                'contacted_date' => now()->subDays(20 - $i),
                'comment' => $def['comment'] ?? null,
                'contact_link' => $def['contact_link'] ?? null,
            ]);

            $activityNotes = [
                'Initial cold outreach message sent.',
                'Followed up via email with more details.',
                'Had a call to discuss requirements.',
                'Sent proposal for review.',
                'Confirmed scope and next steps.',
            ];
            $activityCount = $isClosed ? rand(2, 4) : rand(0, 2);
            for ($a = 0; $a < $activityCount; $a++) {
                LeadActivity::create([
                    'lead_id' => $lead->id,
                    'user_id' => $sales3->id,
                    'note' => $activityNotes[$a % count($activityNotes)],
                ]);
            }

            if ($def['status'] === 'won') {
                $wonClient = Client::create([
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

                if ($i < 3) {
                    $wonProject = Project::create([
                        'client_id' => $wonClient->id,
                        'created_by' => $sales3->id,
                        'assigned_to' => $owner3->id,
                        'name' => $def['company'].' Engagement',
                        'description' => $def['requirement'],
                        'status' => 'active',
                    ]);
                    $wonProject->developers()->attach([$dev1->id]);
                }
            }
        }

        // Ten days of daily cold-outreach leads added to Vishnu's pipeline (10+ contacts/day)
        $dailyLeadTechnologies = ['Web Development', 'Mobile App', 'Digital Marketing', 'E-commerce', 'WordPress', 'Social Media', 'SEO', 'Branding', 'Other'];
        $dailyLeadSources = ['Upwork', 'LinkedIn', 'Cold Email', 'Cold Call', 'Referral', 'Instagram', 'Google Ads', 'Freelancer.com'];
        $dailyLeadCountries = ['USA', 'UK', 'Canada', 'Australia', 'India', 'Germany', 'UAE', 'Singapore', 'South Africa', 'Netherlands', 'Ireland', 'New Zealand'];

        $requirementsByTechnology = [
            'Web Development' => ['Company website redesign', 'Landing page for a product launch', 'Corporate site with a blog', 'Portfolio site rebuild', 'Multi-page brochure website'],
            'Mobile App' => ['iOS/Android app for bookings', 'Delivery tracking app', 'Loyalty app for repeat customers', 'Internal staff scheduling app', 'On-demand service app'],
            'Digital Marketing' => ['Monthly social media management', 'Paid ads campaign setup', 'Content calendar and posting', 'Influencer outreach campaign', 'Email marketing setup'],
            'E-commerce' => ['Online store for retail products', 'Shopify migration and setup', 'Subscription box storefront', 'Marketplace storefront setup', 'Product catalog with checkout'],
            'WordPress' => ['WordPress site build from a template', 'Blog plus membership area', 'WooCommerce store setup', 'Site speed and SEO cleanup', 'Theme redesign refresh'],
            'Social Media' => ['Instagram and Facebook management', 'Short-form video content plan', 'Community management retainer', 'Brand page setup and growth', 'Content creation and scheduling'],
            'SEO' => ['On-page SEO audit and fixes', 'Local SEO for a service business', 'Keyword strategy and content plan', 'Technical SEO cleanup', 'Backlink building campaign'],
            'Branding' => ['Logo and brand identity kit', 'Brand style guide', 'Business card and stationery design', 'Rebrand for a growing company', 'Packaging design refresh'],
            'Other' => ['Custom internal tool', 'CRM setup and integration', 'Automation workflow build', 'Data dashboard build', 'General IT consulting'],
        ];

        $dailyLeadStatusPool = [];
        foreach (['pending' => 40, 'positive' => 20, 'negative' => 15, 'proposal_sent' => 12, 'rejected' => 6, 'won' => 4, 'lost' => 3] as $status => $weight) {
            $dailyLeadStatusPool = array_merge($dailyLeadStatusPool, array_fill(0, $weight, $status));
        }

        for ($day = 9; $day >= 0; $day--) {
            $leadsToday = rand(8, 14);

            for ($n = 0; $n < $leadsToday; $n++) {
                $technology = $dailyLeadTechnologies[array_rand($dailyLeadTechnologies)];
                $requirement = $requirementsByTechnology[$technology][array_rand($requirementsByTechnology[$technology])];
                $name = fake()->name();
                $company = fake()->company();
                $status = $dailyLeadStatusPool[array_rand($dailyLeadStatusPool)];
                $isClosed = in_array($status, Lead::CLOSED_STATUSES, true);
                $country = $dailyLeadCountries[array_rand($dailyLeadCountries)];
                $email = Str::slug($name, '.').'@'.Str::slug($company, '').'.com';

                $lead = Lead::create([
                    'sales_person_id' => $sales3->id,
                    'client_name' => $name,
                    'company_name' => $company,
                    'country' => $country,
                    'email' => $email,
                    'phone' => fake()->e164PhoneNumber(),
                    'requirement' => $requirement,
                    'service_type' => $technology,
                    'source' => $dailyLeadSources[array_rand($dailyLeadSources)],
                    'status' => $status,
                    'budget' => $isClosed ? fake()->numberBetween(800, 9000) : null,
                    'follow_up_date' => $isClosed ? null : now()->addDays(rand(1, 7)),
                    'contacted_date' => now()->subDays($day),
                    'comment' => in_array($status, ['positive', 'negative', 'rejected'], true) ? fake()->sentence(10) : null,
                ]);

                if ($status === 'won') {
                    Client::create([
                        'lead_id' => $lead->id,
                        'sales_person_id' => $sales3->id,
                        'business_name' => $company,
                        'business_type' => $technology,
                        'business_address' => $country,
                        'owner_name' => $name,
                        'owner_designation' => 'Owner',
                        'owner_contact' => $email,
                        'agreement_effective_date' => now()->subDays($day),
                        'agreement_scope_summary' => $requirement,
                    ]);
                }
            }
        }

        // Sales targets (current month, plus a prior-month shortfall for Divya to demonstrate carryforward)
        $prevMonthDate = now()->subMonthNoOverflow();
        SalesTarget::create(['user_id' => $sales2->id, 'month' => $prevMonthDate->month, 'year' => $prevMonthDate->year, 'target_amount' => 6000, 'commission_percent' => 5]);

        SalesTarget::create(['user_id' => $sales1->id, 'month' => now()->month, 'year' => now()->year, 'target_amount' => 10000, 'commission_percent' => 5]);
        SalesTarget::create(['user_id' => $sales2->id, 'month' => now()->month, 'year' => now()->year, 'target_amount' => 8000, 'commission_percent' => 5]);
        SalesTarget::create(['user_id' => $sales3->id, 'month' => now()->month, 'year' => now()->year, 'target_amount' => 12000, 'commission_percent' => 5]);

        // Three months of prior sales-target history, each with a few backdated won deals so
        // "achieved" isn't just zero, showing a mix of over- and under-target months.
        $targetHistoryRanges = [
            $sales1->id => [9000, 13000],
            $sales2->id => [7000, 9000],
            $sales3->id => [11000, 15000],
        ];

        for ($monthsBack = 1; $monthsBack <= 3; $monthsBack++) {
            $periodDate = now()->subMonthsNoOverflow($monthsBack);

            foreach ($targetHistoryRanges as $userId => [$minTarget, $maxTarget]) {
                if (SalesTarget::where('user_id', $userId)->where('month', $periodDate->month)->where('year', $periodDate->year)->exists()) {
                    continue;
                }

                SalesTarget::create([
                    'user_id' => $userId,
                    'month' => $periodDate->month,
                    'year' => $periodDate->year,
                    'target_amount' => rand($minTarget, $maxTarget),
                    'commission_percent' => 5,
                ]);

                for ($dealNum = 0; $dealNum < rand(1, 3); $dealNum++) {
                    $dealName = fake()->name();
                    $dealCompany = fake()->company();
                    $dealTechnology = $dailyLeadTechnologies[array_rand($dailyLeadTechnologies)];
                    $dealDate = $periodDate->copy()->setDay(rand(1, $periodDate->daysInMonth));
                    $dealEmail = Str::slug($dealName, '.').'@'.Str::slug($dealCompany, '').'.com';

                    $historicalLead = Lead::create([
                        'sales_person_id' => $userId,
                        'client_name' => $dealName,
                        'company_name' => $dealCompany,
                        'country' => $dailyLeadCountries[array_rand($dailyLeadCountries)],
                        'email' => $dealEmail,
                        'phone' => fake()->e164PhoneNumber(),
                        'requirement' => $requirementsByTechnology[$dealTechnology][array_rand($requirementsByTechnology[$dealTechnology])],
                        'service_type' => $dealTechnology,
                        'source' => $dailyLeadSources[array_rand($dailyLeadSources)],
                        'status' => 'won',
                        'budget' => rand(2000, 6000),
                        'contacted_date' => $dealDate->copy()->subDays(rand(3, 10)),
                    ]);

                    Lead::where('id', $historicalLead->id)->update(['updated_at' => $dealDate]);

                    Client::create([
                        'lead_id' => $historicalLead->id,
                        'sales_person_id' => $userId,
                        'business_name' => $dealCompany,
                        'business_type' => $dealTechnology,
                        'business_address' => $historicalLead->country,
                        'owner_name' => $dealName,
                        'owner_designation' => 'Owner',
                        'owner_contact' => $dealEmail,
                        'agreement_effective_date' => $dealDate,
                        'agreement_scope_summary' => $historicalLead->requirement,
                    ]);
                }
            }
        }

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
