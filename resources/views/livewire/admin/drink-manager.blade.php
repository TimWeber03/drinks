<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink dark:text-ink-dark">Drinks</h1>
        <button wire:click="create" type="button" class="rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-white hover:bg-accent/90 dark:bg-accent-dark dark:hover:bg-accent-dark/90">
            New drink
        </button>
    </div>

    @if ($showForm)
        <form wire:submit="save" class="mb-6 grid gap-4 rounded-2xl border border-line bg-surface p-6 dark:border-line-dark dark:bg-surface-dark sm:grid-cols-2">
            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="price" value="Price (€)" />
                <x-text-input id="price" type="number" step="0.01" min="0" class="mt-1 block w-full" wire:model="price" />
                <x-input-error :messages="$errors->get('price')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="bottleSize" value="Bottle size (l, optional)" />
                <x-text-input id="bottleSize" type="number" step="0.01" min="0" class="mt-1 block w-full" wire:model="bottleSize" />
                <x-input-error :messages="$errors->get('bottleSize')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="image" value="Image (optional)" />
                <input id="image" type="file" wire:model="image" accept="image/*" class="mt-1 block w-full text-sm text-muted dark:text-muted-dark">
                <x-input-error :messages="$errors->get('image')" class="mt-2" />
            </div>

            <label class="flex items-center gap-2 sm:col-span-2">
                <input type="checkbox" wire:model="active" class="rounded border-line text-accent focus:ring-accent dark:border-line-dark dark:bg-surface-dark">
                <span class="text-sm text-ink dark:text-ink-dark">Active (visible in the kiosk)</span>
            </label>

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
                    <th class="px-4 py-3 font-medium">Price</th>
                    <th class="px-4 py-3 font-medium">Bottle size</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line dark:divide-line-dark">
                @foreach ($drinks as $drink)
                    <tr>
                        <td class="px-4 py-3 font-medium text-ink dark:text-ink-dark">{{ $drink->name }}</td>
                        <td class="px-4 py-3 tabular-nums text-ink dark:text-ink-dark">{{ number_format($drink->price, 2) }} €</td>
                        <td class="px-4 py-3 tabular-nums text-muted dark:text-muted-dark">{{ $drink->bottle_size ? number_format($drink->bottle_size, 2).' l' : '—' }}</td>
                        <td class="px-4 py-3">
                            <button
                                wire:click="toggleActive({{ $drink->id }})"
                                type="button"
                                @class([
                                    'rounded-full px-2.5 py-1 text-xs font-medium',
                                    'bg-positive/10 text-positive dark:bg-positive-dark/10 dark:text-positive-dark' => $drink->active,
                                    'bg-muted/10 text-muted dark:bg-muted-dark/10 dark:text-muted-dark' => ! $drink->active,
                                ])
                            >
                                {{ $drink->active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="edit({{ $drink->id }})" type="button" class="text-accent hover:underline dark:text-accent-dark">
                                Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $drinks->links() }}
    </div>
</div>
