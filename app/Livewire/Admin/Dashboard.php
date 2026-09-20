<?php

namespace App\Livewire\Admin;

use App\Enums\TransactionType;
use App\Models\Drink;
use App\Models\Drinker;
use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'activeDrinkers' => Drinker::query()->where('active', true)->count(),
            'activeDrinks' => Drink::query()->where('active', true)->count(),
            'totalBalance' => Drinker::query()->sum('balance'),
            'todaysSales' => Transaction::query()
                ->where('type', TransactionType::Purchase)
                ->whereDate('created_at', today())
                ->count(),
        ]);
    }
}
