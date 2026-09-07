<?php

namespace App\Livewire\Hr;

use App\Models\Resignation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ResignationIndex extends Component
{
    public bool $showForm = false;

    #[Validate('required|date|after_or_equal:today')]
    public string $last_working_date = '';

    #[Validate('nullable|string|max:1000')]
    public string $reason = '';

    public function openForm(): void
    {
        $this->reset(['last_working_date', 'reason']);
        $this->showForm = true;
    }

    public function submit(): void
    {
        $this->validate();

        Resignation::create([
            'user_id' => Auth::id(),
            'notice_date' => now()->toDateString(),
            'last_working_date' => $this->last_working_date,
            'reason' => $this->reason,
            'status' => 'pending',
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'Resignation submitted to HR.', type: 'success');
    }

    public function render()
    {
        return view('livewire.hr.resignation-index', [
            'resignations' => Resignation::where('user_id', Auth::id())->latest()->get(),
        ]);
    }
}
