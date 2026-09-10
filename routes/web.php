<?php

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(Auth::check() ? '/dashboard' : '/login'));

Route::post('logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'pages.dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    // HR self-service (all employees)
    Route::view('attendance', 'pages.hr.attendance-index')->name('hr.attendance');
    Route::view('leave', 'pages.hr.leave-index')->name('hr.leave');
    Route::view('payslips', 'pages.hr.payslip-index')->name('hr.payslips');
    Route::view('expenses', 'pages.hr.expense-index')->name('hr.expenses');
    Route::view('assets', 'pages.hr.asset-index')->name('hr.assets');
    Route::view('documents', 'pages.hr.document-index')->name('hr.documents');
    Route::view('announcements', 'pages.hr.announcement-index')->name('hr.announcements');
    Route::view('resignation', 'pages.hr.resignation-index')->name('hr.resignation');

    Route::middleware(['role:manager|hr_admin|super_admin'])->group(function () {
        Route::view('org-chart', 'pages.company.org-chart')->name('org-chart');
    });

    // Role-specific daily logs
    Route::middleware(['role:programmer|super_admin'])->group(function () {
        Route::view('timesheets', 'pages.work.timesheet-index')->name('work.timesheets');
    });
    Route::middleware(['role:marketer|super_admin'])->group(function () {
        Route::view('marketing-logs', 'pages.work.marketing-log-index')->name('work.marketing-logs');
    });

    // Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::middleware(['role:sales_exec|marketer|super_admin'])->group(function () {
            Route::view('leads', 'pages.sales.lead-pipeline')->name('leads');
            Route::get('leads/{lead}', function (Lead $lead) {
                return view('pages.sales.lead-show', compact('lead'));
            })->name('leads.show');
        });

        Route::middleware(['role:sales_exec|manager|super_admin'])->group(function () {
            Route::view('clients', 'pages.sales.client-index')->name('clients');
            Route::get('clients/{client}', function (Client $client) {
                return view('pages.sales.client-show', compact('client'));
            })->name('clients.show');
        });

        Route::middleware(['role:sales_exec|manager|super_admin'])->group(function () {
            Route::view('targets', 'pages.sales.target-dashboard')->name('targets');
            Route::get('targets/{user}/report/{year}/{month}', function (User $user, int $year, int $month) {
                return view('pages.sales.target-report', compact('user', 'year', 'month'));
            })->name('targets.report');
        });
    });

    // HR Admin
    Route::middleware(['role:hr_admin|super_admin'])->prefix('hr-admin')->name('hradmin.')->group(function () {
        Route::view('employees', 'pages.hr-admin.employee-index')->name('employees');
        Route::get('employees/{user}', function (User $user) {
            return view('pages.hr-admin.employee-show', compact('user'));
        })->name('employees.show');
        Route::view('leave-approvals', 'pages.hr-admin.leave-approvals')->name('leave-approvals');
        Route::view('holidays', 'pages.hr-admin.holiday-index')->name('holidays');
        Route::view('resignations', 'pages.hr-admin.resignation-approvals')->name('resignations');
        Route::view('policies', 'pages.hr-admin.policy-index')->name('policies');
        Route::view('reports', 'pages.hr-admin.hr-reports')->name('reports');
    });

    // Finance Admin
    Route::middleware(['role:finance_admin|super_admin'])->prefix('finance')->name('finance.')->group(function () {
        Route::view('billing-requests', 'pages.finance.billing-request-index')->name('billing-requests');
        Route::view('invoices', 'pages.finance.invoice-index')->name('invoices');
        Route::get('invoices/{invoice}', function (Invoice $invoice) {
            return view('pages.finance.invoice-show', compact('invoice'));
        })->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', \App\Http\Controllers\InvoicePdfController::class)->name('invoices.pdf');
        Route::view('expenses', 'pages.finance.expense-approvals')->name('expenses');
        Route::view('payroll', 'pages.finance.payroll-run')->name('payroll');
        Route::view('reports', 'pages.finance.financial-reports')->name('reports');
    });

    // Super Admin
    Route::middleware(['role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::view('users', 'pages.admin.user-management')->name('users');
        Route::view('settings/lead-sources', 'pages.admin.lead-source-management')->name('lead-sources');
        Route::view('settings/roles', 'pages.admin.role-management')->name('roles');
    });
});

require __DIR__.'/auth.php';
