<?php

namespace App\Livewire\Hr;

use App\Mail\ResignationSubmitted;
use App\Models\Resignation;
use App\Support\Html;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ResignationIndex extends Component
{
    public bool $showForm = false;

    #[Validate('required|date|after_or_equal:today')]
    public string $last_working_date = '';

    #[Validate('nullable|string|max:20000')]
    public string $reason = '';

    #[Validate('boolean')]
    public bool $cc_personal_email = false;

    #[Validate('nullable|email|max:255|required_if:cc_personal_email,true')]
    public string $personal_email = '';

    // Bumped on every openForm() call so the rich-text editor's wire:ignore
    // container is forced to remount with fresh (empty) content.
    public int $formInstance = 0;

    public function openForm(): void
    {
        $this->formInstance++;
        $this->reset(['last_working_date', 'reason', 'cc_personal_email', 'personal_email']);
        $this->resetValidation();
        $this->showForm = true;
    }

    public function submit(): void
    {
        $this->validate();

        $ccEmail = $this->cc_personal_email ? $this->personal_email : null;

        $resignation = Resignation::create([
            'user_id' => Auth::id(),
            'notice_date' => now()->toDateString(),
            'last_working_date' => $this->last_working_date,
            'reason' => Html::sanitize($this->reason),
            'cc_email' => $ccEmail,
            'status' => 'pending',
        ]);

        try {
            $mail = Mail::to(config('mail.hr_notification_address'));

            if ($ccEmail) {
                $mail->cc($ccEmail);
            }

            $mail->send(new ResignationSubmitted($resignation));
        } catch (\Throwable $e) {
            report($e);
            $this->showForm = false;
            $this->dispatch('toast', message: 'Resignation submitted, but the notification email to HR could not be sent.', type: 'error');

            return;
        }

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
