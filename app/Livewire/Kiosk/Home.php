<?php

namespace App\Livewire\Kiosk;

use App\Exceptions\OutOfStockException;
use App\Models\Drink;
use App\Models\Drinker;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.kiosk')]
class Home extends Component
{
    public string $search = '';

    public ?int $selectedDrinkerId = null;

    /** @var 'drinkers'|'drinks'|'deposit'|'confirmation' */
    public string $view = 'drinkers';

    public string $confirmationMessage = '';

    public string $depositAmount = '';

    public bool $showOutOfStock = true;

    public function selectDrinker(int $drinkerId): void
    {
        $this->selectedDrinkerId = $drinkerId;
        $this->view = 'drinks';
    }

    public function buy(int $drinkId): void
    {
        $this->resetErrorBag('stock');

        $drinker = Drinker::findOrFail($this->selectedDrinkerId);
        $drink = Drink::findOrFail($drinkId);

        try {
            $drinker->buy($drink);
        } catch (OutOfStockException) {
            $this->addError('stock', "{$drink->name} just sold out.");

            return;
        }

        $this->confirmationMessage = "{$drinker->name} bought a {$drink->name} for ".number_format($drink->price, 2).' €';
        $this->view = 'confirmation';
    }

    public function showDeposit(): void
    {
        $this->resetErrorBag();
        $this->depositAmount = '';
        $this->view = 'deposit';
    }

    public function depositQuickAmount(float $amount): void
    {
        $this->makeDeposit($amount);
    }

    public function depositCustomAmount(): void
    {
        $this->makeDeposit((float) $this->depositAmount);
    }

    protected function makeDeposit(float $amount): void
    {
        if ($amount <= 0) {
            $this->addError('depositAmount', 'Enter an amount greater than zero.');

            return;
        }

        $drinker = Drinker::findOrFail($this->selectedDrinkerId);
        $drinker->deposit($amount);

        $this->confirmationMessage = "{$drinker->name} deposited ".number_format($amount, 2).' €';
        $this->view = 'confirmation';
    }

    public function backToDrinks(): void
    {
        $this->resetErrorBag();
        $this->view = 'drinks';
    }

    public function backToDrinkers(): void
    {
        $this->reset(['selectedDrinkerId', 'view', 'confirmationMessage', 'depositAmount']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.kiosk.home', [
            'drinkers' => Drinker::query()
                ->where('active', true)
                ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
                ->orderBy('name')
                ->get(),
            'selectedDrinker' => $this->selectedDrinkerId
                ? Drinker::find($this->selectedDrinkerId)
                : null,
            'drinks' => Drink::query()
                ->where('active', true)
                ->when(! $this->showOutOfStock, fn ($query) => $query->where(
                    fn ($q) => $q->where('stock_tracked', false)->orWhere('stock', '>', 0)
                ))
                ->orderBy('name')
                ->get(),
        ]);
    }
}
