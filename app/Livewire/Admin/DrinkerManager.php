<?php

namespace App\Livewire\Admin;

use App\Models\Drinker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class DrinkerManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public $avatar = null;

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
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $attributes = [
            'name' => $data['name'],
            'active' => $this->active,
        ];

        $drinker = $this->editingId ? Drinker::findOrFail($this->editingId) : null;

        if ($this->avatar) {
            if ($drinker?->avatar_path) {
                Storage::disk('public')->delete($drinker->avatar_path);
            }

            $attributes['avatar_path'] = $this->avatar->store('avatars', 'public');
        }

        if ($drinker) {
            $drinker->update($attributes);
        } else {
            Drinker::create($attributes + ['balance' => 0]);
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
        $this->reset(['editingId', 'name', 'avatar', 'showForm']);
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
