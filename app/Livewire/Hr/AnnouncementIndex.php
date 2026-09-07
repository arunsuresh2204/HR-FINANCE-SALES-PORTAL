<?php

namespace App\Livewire\Hr;

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class AnnouncementIndex extends Component
{
    use WithPagination;

    public bool $showForm = false;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('required|string|max:2000')]
    public string $body = '';

    public bool $pinned = false;

    public function openForm(): void
    {
        $this->reset(['title', 'body', 'pinned']);
        $this->showForm = true;
    }

    public function submit(): void
    {
        abort_unless(Auth::user()->isHrAdmin(), 403);

        $this->validate();

        Announcement::create([
            'posted_by' => Auth::id(),
            'title' => $this->title,
            'body' => $this->body,
            'pinned' => $this->pinned,
        ]);

        $this->showForm = false;
        $this->dispatch('toast', message: 'Announcement posted.', type: 'success');
    }

    public function delete(Announcement $announcement): void
    {
        abort_unless(Auth::user()->isHrAdmin(), 403);

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
