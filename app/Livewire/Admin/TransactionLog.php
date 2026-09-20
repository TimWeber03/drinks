<?php

namespace App\Livewire\Admin;

use App\Enums\TransactionType;
use App\Models\Drink;
use App\Models\Drinker;
use App\Models\Transaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TransactionLog extends Component
{
    use WithPagination;

    #[Url]
    public string $drinkerId = '';

    #[Url]
    public string $drinkId = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['drinkerId', 'drinkId', 'type', 'from', 'to'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['drinkerId', 'drinkId', 'type', 'from', 'to']);
    }

    public function render()
    {
        $transactions = Transaction::query()
            ->with(['drinker', 'drink', 'createdBy'])
            ->when($this->drinkerId !== '', fn ($query) => $query->where('drinker_id', $this->drinkerId))
            ->when($this->drinkId !== '', fn ($query) => $query->where('drink_id', $this->drinkId))
            ->when($this->type !== '', fn ($query) => $query->where('type', $this->type))
            ->when($this->from !== '', fn ($query) => $query->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn ($query) => $query->whereDate('created_at', '<=', $this->to))
            ->latest()
            ->paginate(25);

        return view('livewire.admin.transaction-log', [
            'transactions' => $transactions,
            'drinkers' => Drinker::query()->orderBy('name')->get(),
            'drinks' => Drink::query()->orderBy('name')->get(),
            'types' => TransactionType::cases(),
        ]);
    }
}
