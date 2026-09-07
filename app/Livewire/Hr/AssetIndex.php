<?php

namespace App\Livewire\Hr;

use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AssetIndex extends Component
{
    public function render()
    {
        return view('livewire.hr.asset-index', [
            'assets' => Asset::where('user_id', Auth::id())->orderByDesc('assigned_date')->get(),
        ]);
    }
}
