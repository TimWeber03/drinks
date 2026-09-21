<?php

namespace App\Livewire\Admin;

use App\Models\Drink;
use App\Models\Image;
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

    public bool $stockTracked = false;

    public string $stock = '';

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
        $this->stockTracked = $drink->stock_tracked;
        $this->stock = (string) $drink->stock;
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
            'stock' => ['nullable', 'integer', 'min:0'],
        ]);

        $attributes = [
            'name' => $data['name'],
            'price' => $data['price'],
            'bottle_size' => $data['bottleSize'] !== '' ? $data['bottleSize'] : null,
            'active' => $this->active,
            'stock_tracked' => $this->stockTracked,
            'stock' => $this->stockTracked ? (int) $data['stock'] : 0,
        ];

        $drink = $this->editingId ? Drink::findOrFail($this->editingId) : null;

        if ($this->image) {
            $drink?->image?->deleteWithFile();

            $attributes['image_id'] = Image::storeUploadedFile($this->image, 'drinks')->id;
        }

        if ($drink) {
            $drink->update($attributes);
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
        $this->reset(['editingId', 'name', 'price', 'bottleSize', 'image', 'showForm', 'stockTracked', 'stock']);
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
