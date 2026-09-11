<?php

namespace App\Livewire\Hr;

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AnnouncementIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    public bool $showForm = false;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string|max:2000')]
    public string $body = '';

    public bool $pinned = false;

    #[Validate('nullable|file|max:5120|mimes:jpg,jpeg,png,pdf')]
    public $attachment = null;

    public function openForm(): void
    {
        $this->reset(['title', 'body', 'pinned', 'attachment']);
        $this->showForm = true;
    }

    public function submit(): void
    {
        abort_unless(Auth::user()->can('access_hr_admin'), 403);

        $this->validate();

        Announcement::create([
            'posted_by' => Auth::id(),
            'title' => $this->title,
            'body' => $this->body,
            'pinned' => $this->pinned,
            'attachment_path' => $this->attachment?->store('announcements', 'public'),
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'Announcement posted.', type: 'success');
    }

    public function delete(Announcement $announcement): void
    {
        abort_unless(Auth::user()->can('access_hr_admin'), 403);

        $announcement->delete();
        $this->dispatch('toast', message: 'Announcement removed.', type: 'success');
    }

    public function render()
    {
        return view('livewire.hr.announcement-index', [
            'announcements' => Announcement::orderByDesc('pinned')->latest()->paginate(8),
        ]);
    }
}
