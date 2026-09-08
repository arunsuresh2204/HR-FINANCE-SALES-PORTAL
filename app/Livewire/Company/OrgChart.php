<?php

namespace App\Livewire\Company;

use App\Models\User;
use Livewire\Component;

class OrgChart extends Component
{
    public function render()
    {
        $users = User::with('additionalManagers')->orderBy('name')->get();

        $roots = $users->filter(fn (User $u) => $u->manager_id === null)->values();

        return view('livewire.company.org-chart', [
            'roots' => $roots,
            'users' => $users,
        ]);
    }
}
