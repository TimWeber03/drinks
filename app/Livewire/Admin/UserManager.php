<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class UserManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public function create(): void
    {
        $this->reset(['name', 'email', 'password']);
        $this->showForm = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        $this->reset(['name', 'email', 'password', 'showForm']);
    }

    public function cancel(): void
    {
        $this->reset(['name', 'email', 'password', 'showForm']);
        $this->resetErrorBag();
    }

    public function delete(User $user): void
    {
        if ($user->id === Auth::id()) {
            $this->addError('delete', 'You cannot delete your own account.');

            return;
        }

        $user->delete();
    }

    public function render()
    {
        return view('livewire.admin.user-manager', [
            'admins' => User::query()->orderBy('name')->paginate(15),
        ]);
    }
}
