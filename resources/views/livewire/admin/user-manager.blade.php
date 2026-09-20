<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink dark:text-ink-dark">Admins</h1>
        <button wire:click="create" type="button" class="rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-white hover:bg-accent/90 dark:bg-accent-dark dark:hover:bg-accent-dark/90">
            New admin
        </button>
    </div>

    <x-input-error :messages="$errors->get('delete')" class="mb-4" />

    @if ($showForm)
        <form wire:submit="save" class="mb-6 grid gap-4 rounded-2xl border border-line bg-surface p-6 dark:border-line-dark dark:bg-surface-dark sm:grid-cols-2">
            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" type="email" class="mt-1 block w-full" wire:model="email" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" type="password" class="mt-1 block w-full" wire:model="password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex gap-3 sm:col-span-2">
                <x-primary-button type="submit">Save</x-primary-button>
                <x-secondary-button wire:click="cancel" type="button">Cancel</x-secondary-button>
            </div>
        </form>
    @endif

    <div class="overflow-x-auto rounded-2xl border border-line bg-surface dark:border-line-dark dark:bg-surface-dark">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted dark:border-line-dark dark:text-muted-dark">
                <tr>
                    <th class="px-4 py-3 font-medium">Name</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line dark:divide-line-dark">
                @foreach ($admins as $admin)
                    <tr>
                        <td class="px-4 py-3 font-medium text-ink dark:text-ink-dark">{{ $admin->name }}</td>
                        <td class="px-4 py-3 text-muted dark:text-muted-dark">{{ $admin->email }}</td>
                        <td class="px-4 py-3 text-right">
                            <button
                                wire:click="delete({{ $admin->id }})"
                                wire:confirm="Remove this admin account?"
                                type="button"
                                class="text-negative hover:underline dark:text-negative-dark"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $admins->links() }}
    </div>
</div>
