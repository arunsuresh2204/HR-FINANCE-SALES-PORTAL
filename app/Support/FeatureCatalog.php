<?php

namespace App\Support;

class FeatureCatalog
{
    /**
     * Every feature area the app gates access to, keyed by the permission
     * name (spatie/laravel-permission) checked in routes/web.php.
     */
    public const FEATURES = [
        'access_org_chart' => 'Org Chart',
        'access_timesheets' => 'Timesheets',
        'access_marketing_logs' => 'Marketing Logs',
        'access_sales_leads' => 'Sales Leads',
        'access_sales_clients' => 'Sales Clients',
        'access_sales_targets' => 'Sales Targets',
        'access_hr_admin' => 'HR Admin',
        'access_finance_admin' => 'Finance Admin',
        'access_super_admin' => 'Super Admin',
    ];

    /**
     * Longer descriptions shown as tooltips in the Feature Access matrix.
     */
    public const FEATURE_DESCRIPTIONS = [
        'access_org_chart' => 'Company org chart and reporting lines',
        'access_timesheets' => 'Programmer daily timesheet logs',
        'access_marketing_logs' => 'Marketer daily activity logs',
        'access_sales_leads' => 'Leads pipeline (add/track cold-outreach contacts)',
        'access_sales_clients' => 'Client list and client detail pages',
        'access_sales_targets' => 'Sales targets dashboard and reports',
        'access_hr_admin' => 'Employees, leave approvals, attendance oversight, holidays, resignations, policies, HR reports',
        'access_finance_admin' => 'Billing requests, invoices, expense approvals, payroll, financial reports',
        'access_super_admin' => 'User management, lead sources, functional roles',
    ];

    /**
     * Starting permission set per seeded role, matching the access each
     * role already had under the old hardcoded route:role|role middleware
     * so switching over doesn't change anyone's access.
     */
    public const DEFAULT_ROLE_PERMISSIONS = [
        'super_admin' => [
            'access_org_chart', 'access_timesheets', 'access_marketing_logs',
            'access_sales_leads', 'access_sales_clients', 'access_sales_targets',
            'access_hr_admin', 'access_finance_admin', 'access_super_admin',
        ],
        'manager_engineering' => ['access_org_chart', 'access_sales_clients', 'access_sales_targets'],
        'hr_admin' => ['access_org_chart', 'access_hr_admin'],
        'finance_admin' => ['access_finance_admin'],
        'sales_exec' => ['access_sales_leads', 'access_sales_clients', 'access_sales_targets'],
        'marketer' => ['access_marketing_logs', 'access_sales_leads'],
        'programmer' => ['access_timesheets'],
        'team_lead' => [],
    ];
}
