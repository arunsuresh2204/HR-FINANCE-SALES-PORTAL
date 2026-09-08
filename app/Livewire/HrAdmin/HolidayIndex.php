<?php

namespace App\Livewire\HrAdmin;

use App\Models\Holiday;
use Livewire\Attributes\Validate;
use Livewire\Component;

class HolidayIndex extends Component
{
    public bool $showForm = false;

    #[Validate('required|date')]
    public string $date = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    public function openForm(): void
    {
        $this->reset(['date', 'name']);
        $this->resetValidation();
        $this->showForm = true;
    }

    public function submit(): void
    {
        $this->validate([
            'date' => 'required|date|unique:holidays,date',
            'name' => 'required|string|max:255',
        ]);

        Holiday::create([
            'date' => $this->date,
            'name' => $this->name,
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'Holiday added to the calendar.', type: 'success');
    }

    public function delete(Holiday $holiday): void
    {
        $holiday->delete();
        $this->dispatch('toast', message: 'Holiday removed from the calendar.', type: 'success');
    }

    public function render()
    {
        return view('livewire.hr-admin.holiday-index', [
            'holidays' => Holiday::orderBy('date')->get()->groupBy(fn ($holiday) => $holiday->date->format('Y')),
        ]);
    }
}
