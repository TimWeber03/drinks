<?php

namespace App\Livewire\Kiosk;

use App\Models\Drinker;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.kiosk')]
class DrinkerHistory extends Component
{
    use WithPagination;

    public Drinker $drinker;

    public function mount(Drinker $drinker): void
    {
        $this->drinker = $drinker;
    }

    public function render()
    {
        return view('livewire.kiosk.drinker-history', [
            'transactions' => $this->drinker->transactions()
                ->with('drink')
                ->latest()
                ->paginate(20),
        ]);
    }
}
