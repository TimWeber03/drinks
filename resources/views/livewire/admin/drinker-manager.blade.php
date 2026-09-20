<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink dark:text-ink-dark">Drinkers</h1>
        <button wire:click="create" type="button" class="rounded-xl bg-accent px-4 py-2 text-sm font-semibold text-white hover:bg-accent/90 dark:bg-accent-dark dark:hover:bg-accent-dark/90">
            New drinker
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
                <x-input-label for="avatar" value="Avatar (optional)" />
                <input id="avatar" type="file" wire:model="avatar" accept="image/*" class="mt-1 block w-full text-sm text-muted dark:text-muted-dark">
                <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
            </div>

            <label class="flex items-center gap-2 self-end">
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
                    <th class="px-4 py-3 font-medium">Balance</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line dark:divide-line-dark">
                @foreach ($drinkers as $drinker)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($drinker->avatar_path)
                                    <img src="{{ asset('storage/'.$drinker->avatar_path) }}" alt="{{ $drinker->name }}" class="h-8 w-8 rounded-full object-cover">
                                @else
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-accent/10 text-xs font-semibold text-accent dark:bg-accent-dark/10 dark:text-accent-dark">
                                        {{ mb_strtoupper(mb_substr($drinker->name, 0, 1)) }}
                                    </span>
                                @endif
                                <span class="font-medium text-ink dark:text-ink-dark">{{ $drinker->name }}</span>
                            </div>
                        </td>
                        <td @class([
                            'px-4 py-3 tabular-nums',
                            'text-ink dark:text-ink-dark' => $drinker->balance >= 0,
                            'font-semibold text-negative dark:text-negative-dark' => $drinker->balance < 0,
                        ])>
                            {{ number_format($drinker->balance, 2) }} €
                        </td>
                        <td class="px-4 py-3">
                            <button
                                wire:click="toggleActive({{ $drinker->id }})"
                                type="button"
                                @class([
                                    'rounded-full px-2.5 py-1 text-xs font-medium',
                                    'bg-positive/10 text-positive dark:bg-positive-dark/10 dark:text-positive-dark' => $drinker->active,
                                    'bg-muted/10 text-muted dark:bg-muted-dark/10 dark:text-muted-dark' => ! $drinker->active,
                                ])
                            >
                                {{ $drinker->active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-3">
                                <button wire:click="startAdjustment({{ $drinker->id }})" type="button" class="text-accent hover:underline dark:text-accent-dark">
                                    Adjust balance
                                </button>
                                <button wire:click="edit({{ $drinker->id }})" type="button" class="text-accent hover:underline dark:text-accent-dark">
                                    Edit
                                </button>
                            </div>

                            @if ($adjustingId === $drinker->id)
                                <form wire:submit="applyAdjustment" class="mt-2 flex items-center justify-end gap-2">
                                    <input
                                        type="number"
                                        step="0.01"
                                        wire:model="adjustmentAmount"
                                        placeholder="+/- amount"
                                        class="w-28 rounded-lg border-line bg-surface text-sm text-ink focus:border-accent focus:ring-accent dark:border-line-dark dark:bg-surface-dark dark:text-ink-dark"
                                    >
                                    <button type="submit" class="rounded-lg bg-accent px-3 py-1.5 text-sm font-medium text-white hover:bg-accent/90 dark:bg-accent-dark dark:hover:bg-accent-dark/90">
                                        Apply
                                    </button>
                                    <button wire:click="cancelAdjustment" type="button" class="text-sm text-muted hover:text-ink dark:text-muted-dark dark:hover:text-ink-dark">
                                        Cancel
                                    </button>
                                </form>
                                <x-input-error :messages="$errors->get('adjustmentAmount')" class="mt-1 text-right" />
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $drinkers->links() }}
    </div>
</div>
