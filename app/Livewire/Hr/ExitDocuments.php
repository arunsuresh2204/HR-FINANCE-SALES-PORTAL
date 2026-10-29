<?php

namespace App\Livewire\Hr;

use App\Models\Payroll;
use App\Models\Resignation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ExitDocuments extends Component
{
    public function render()
    {
        return view('livewire.hr.exit-documents', [
            'resignation' => Resignation::where('user_id', Auth::id())->whereIn('status', ['accepted', 'completed'])->latest()->first(),
            'payrolls' => Payroll::where('user_id', Auth::id())->orderByDesc('year')->orderByDesc('month')->limit(6)->get(),
        ]);
    }
}
