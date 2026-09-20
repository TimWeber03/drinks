<?php

namespace App\Livewire\Admin;

use App\Models\Drinker;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class DrinkerManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public bool $active = true;

    public ?int $adjustingId = null;

    public string $adjustmentAmount = '';

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Drinker $drinker): void
    {
        $this->editingId = $drinker->id;
        $this->name = $drinker->name;
        $this->active = $drinker->active;
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($this->editingId) {
            Drinker::findOrFail($this->editingId)->update([
                'name' => $data['name'],
                'active' => $this->active,
            ]);
        } else {
            Drinker::create([
                'name' => $data['name'],
                'active' => $this->active,
                'balance' => 0,
            ]);
        }

        $this->resetForm();
    }

    public function toggleActive(Drinker $drinker): void
    {
        $drinker->update(['active' => ! $drinker->active]);
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function startAdjustment(int $drinkerId): void
    {
        $this->adjustingId = $drinkerId;
        $this->adjustmentAmount = '';
        $this->resetErrorBag();
    }

    public function cancelAdjustment(): void
    {
        $this->adjustingId = null;
        $this->adjustmentAmount = '';
        $this->resetErrorBag();
    }

    public function applyAdjustment(): void
    {
        $this->validate([
            'adjustmentAmount' => ['required', 'numeric', 'not_in:0'],
        ]);

        $drinker = Drinker::findOrFail($this->adjustingId);
        $drinker->adjustBalance((float) $this->adjustmentAmount, Auth::user());

        $this->cancelAdjustment();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'showForm']);
        $this->active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.admin.drinker-manager', [
            'drinkers' => Drinker::query()->orderBy('name')->paginate(15),
        ]);
    }
}
