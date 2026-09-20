<?php

namespace App\Livewire\Admin;

use App\Models\Drink;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class DrinkManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $price = '';

    public string $bottleSize = '';

    public $image = null;

    public bool $active = true;

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(Drink $drink): void
    {
        $this->editingId = $drink->id;
        $this->name = $drink->name;
        $this->price = (string) $drink->price;
        $this->bottleSize = $drink->bottle_size !== null ? (string) $drink->bottle_size : '';
        $this->active = $drink->active;
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'bottleSize' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $attributes = [
            'name' => $data['name'],
            'price' => $data['price'],
            'bottle_size' => $data['bottleSize'] !== '' ? $data['bottleSize'] : null,
            'active' => $this->active,
        ];

        if ($this->image) {
            $attributes['image_path'] = $this->image->store('drinks', 'public');
        }

        if ($this->editingId) {
            Drink::findOrFail($this->editingId)->update($attributes);
        } else {
            Drink::create($attributes);
        }

        $this->resetForm();
    }

    public function toggleActive(Drink $drink): void
    {
        $drink->update(['active' => ! $drink->active]);
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'price', 'bottleSize', 'image', 'showForm']);
        $this->active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.admin.drink-manager', [
            'drinks' => Drink::query()->orderBy('name')->paginate(15),
        ]);
    }
}
