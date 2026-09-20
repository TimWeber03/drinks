<div>
    <a href="{{ route('kiosk.home') }}" wire:navigate class="text-sm text-accent hover:underline dark:text-accent-dark">
        &larr; Back to kiosk
    </a>

    <div class="mt-2 mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink dark:text-ink-dark">{{ $drinker->name }}'s history</h1>
        <span @class([
            'text-lg font-semibold tabular-nums',
            'text-ink dark:text-ink-dark' => $drinker->balance >= 0,
            'text-negative dark:text-negative-dark' => $drinker->balance < 0,
        ])>
            Balance: {{ number_format($drinker->balance, 2) }} €
        </span>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-line bg-surface dark:border-line-dark dark:bg-surface-dark">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted dark:border-line-dark dark:text-muted-dark">
                <tr>
                    <th class="px-4 py-3 font-medium">When</th>
                    <th class="px-4 py-3 font-medium">Type</th>
                    <th class="px-4 py-3 font-medium">Detail</th>
                    <th class="px-4 py-3 text-right font-medium">Amount</th>
                    <th class="px-4 py-3 text-right font-medium">Balance after</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line dark:divide-line-dark">
                @forelse ($transactions as $transaction)
                    <tr>
                        <td class="px-4 py-3 text-muted dark:text-muted-dark">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3 text-ink dark:text-ink-dark">{{ $transaction->type->label() }}</td>
                        <td class="px-4 py-3 text-ink dark:text-ink-dark">{{ $transaction->drink?->name ?? '—' }}</td>
                        <td @class([
                            'px-4 py-3 text-right font-medium tabular-nums',
                            'text-positive dark:text-positive-dark' => $transaction->amount > 0,
                            'text-negative dark:text-negative-dark' => $transaction->amount < 0,
                        ])>
                            {{ number_format($transaction->amount, 2) }} €
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-ink dark:text-ink-dark">{{ number_format($transaction->balance_after, 2) }} €</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-muted dark:text-muted-dark">No activity yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
</div>
