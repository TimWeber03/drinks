<?php

namespace App\Livewire\Kiosk;

use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.kiosk')]
class Activity extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.kiosk.activity', [
            'transactions' => Transaction::query()
                ->with(['drinker', 'drink'])
                ->latest()
                ->paginate(25),
        ]);
    }
}
