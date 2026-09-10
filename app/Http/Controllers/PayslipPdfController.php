<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PayslipPdfController extends Controller
{
    public function __invoke(Payroll $payroll)
    {
        $user = Auth::user();

        abort_unless($user->id === $payroll->user_id || $user->hasAnyRole(['finance_admin', 'super_admin']), 403);
        abort_unless($payroll->payslip_file && Storage::disk('public')->exists($payroll->payslip_file), 404);

        $payroll->loadMissing('user');
        $period = Carbon::create($payroll->year, $payroll->month, 1)->format('M-Y');
        $filename = "Payslip-{$payroll->user->employee_code}-{$period}.pdf";

        return Storage::disk('public')->download($payroll->payslip_file, $filename);
    }
}
