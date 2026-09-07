<?php

namespace App\Livewire\Hr;

use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class PayslipIndex extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.hr.payslip-index', [
            'payrolls' => Payroll::where('user_id', Auth::id())->orderByDesc('year')->orderByDesc('month')->paginate(12),
        ]);
    }
}
