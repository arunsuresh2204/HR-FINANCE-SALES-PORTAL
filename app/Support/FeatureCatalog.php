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
     * A modifier, not a page: combined with one of the FEATURES above, it
     * scopes that page's data to the role's full reporting line (direct and
     * indirect reports) instead of just their own. Kept separate from
     * FEATURES since it grants no route access by itself. Rendered as its
     * own toggle in the Feature Access matrix.
     */
    public const TEAM_MANAGER_PERMISSION = 'manages_team';

    public const TEAM_MANAGER_LABEL = 'Team Manager';

    public const TEAM_MANAGER_DESCRIPTION = 'Not a page by itself — combine with a feature above (e.g. Timesheets, Sales Targets) so this role sees its whole reporting line\'s data there, not just its own.';

    /**
     * Longer descriptions shown as tooltips in the Feature Access matrix.
     */
    public const FEATURE_DESCRIPTIONS = [
        'access_org_chart' => 'Company org chart and reporting lines',
        'access_timesheets' => 'Daily timesheet logs',
        'access_marketing_logs' => 'Marketer daily activity logs',
        'access_sales_leads' => 'Leads pipeline (add/track cold-outreach contacts)',
        'access_sales_clients' => 'Client list and client detail pages',
        'access_sales_targets' => 'Sales targets dashboard and reports',
        'access_hr_admin' => 'Employees, leave approvals, attendance oversight, holidays, resignations, policies, HR reports',
        'access_finance_admin' => 'Billing requests, invoices, expense approvals, payroll, financial reports',
        'access_super_admin' => 'User management, lead sources, functional roles',
    ];

    /**
     * Starting permission set per seeded role. Re-run only for initial
     * setup — it overwrites any manual Feature Access customization.
     */
    public const DEFAULT_ROLE_PERMISSIONS = [
        'super_admin' => [
            'access_org_chart', 'access_timesheets', 'access_marketing_logs',
            'access_sales_leads', 'access_sales_clients', 'access_sales_targets',
            'access_hr_admin', 'access_finance_admin', 'access_super_admin',
            self::TEAM_MANAGER_PERMISSION,
        ],
        'manager_engineering' => ['access_org_chart', 'access_sales_clients', 'access_timesheets', self::TEAM_MANAGER_PERMISSION],
        'hr_admin' => ['access_org_chart', 'access_hr_admin'],
        'finance_admin' => ['access_finance_admin'],
        'sales_exec' => ['access_sales_leads', 'access_sales_clients', 'access_sales_targets'],
        'digital_marketer' => ['access_marketing_logs', 'access_sales_leads'],
        'programmer' => ['access_timesheets'],
        'team_lead_it' => [],
    ];
}
