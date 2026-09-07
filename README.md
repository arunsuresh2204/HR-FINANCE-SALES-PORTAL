# Nexstarc Portal

A unified HR, Finance & Sales management portal for Nexstarc Technologies, built with Laravel 11, Livewire 3, and Tailwind CSS.

Every person is an employee first (personal profile, attendance, leave, payslips) and gets additional dashboard sections based on the functional role(s) assigned to their account: **Programmer**, **Marketer**, **Sales Executive**, **HR Admin**, **Finance Admin**, or **Super Admin**. Roles are managed with [spatie/laravel-permission](https://spatie.be/docs/laravel-permission).

## Modules

- **HR** — employee profiles, attendance clock-in/out, leave requests & approvals, payslips, expense claims, company assets, personal & policy documents, announcements, resignation & offboarding checklists.
- **Sales** — lead pipeline (kanban), lead-to-client conversion, client profiles with signed agreements, billing requests, sales targets & performance.
- **Finance** — converts billing requests into invoices (PDF), tracks payments, approves expenses, runs monthly payroll (with payslip PDFs), and reports on revenue, outstanding invoices, and P&L.

## Stack

- Laravel 11 (PHP 8.2+)
- Livewire 3 + Alpine.js for interactivity
- Tailwind CSS (custom "liquid glass" design system using the Nexstarc brand palette)
- spatie/laravel-permission for roles
- barryvdh/laravel-dompdf for invoice/payslip PDFs
- SQLite by default (swap `DB_CONNECTION` in `.env` for MySQL/Postgres in production)

## Getting started

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

touch database/database.sqlite
php artisan migrate --seed

npm run build   # or `npm run dev` while developing
php artisan serve
```

The seeder creates the example 9-person team from the project spec. All seeded accounts use the password `password`:

| Email | Roles |
|---|---|
| arun@nexstarc.com | Super Admin, Programmer |
| rahul@nexstarc.com | Super Admin, Programmer |
| priya@nexstarc.com | Super Admin, Sales Exec, Finance Admin |
| karthik@nexstarc.com | Super Admin, Sales Exec, HR Admin |
| sneha@nexstarc.com | Programmer |
| vikram@nexstarc.com | Programmer |
| anjali@nexstarc.com | Marketer |
| rohan@nexstarc.com | Sales Exec |
| divya@nexstarc.com | Sales Exec |

New employees are onboarded from **HR Admin → Employees** or **Users & Roles** (Super Admin), which generates the account and a temporary password.
