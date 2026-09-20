<?php

namespace App\Console\Commands;

use App\Enums\TransactionType;
use App\Models\Drink;
use App\Models\Drinker;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Throwable;

#[Signature('mete:import {--dry-run : Report what would be imported without writing anything}')]
#[Description('Import drinkers, drinks, and transaction history from an existing mete installation')]
class MeteImport extends Command
{
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        try {
            DB::connection('mete')->getPdo();
        } catch (Throwable $e) {
            $this->components->error(
                'Could not connect to the mete database. Configure METE_DB_* in your .env file. '.$e->getMessage()
            );

            return self::FAILURE;
        }

        $sourceDrinks = DB::connection('mete')->table('drinks')->orderBy('id')->get();
        $sourceDrinkers = DB::connection('mete')->table('users')->orderBy('id')->get();
        $sourceAudits = DB::connection('mete')->table('audits')->orderBy('user')->orderBy('created_at')->orderBy('id')->get();

        $knownDrinkIds = $sourceDrinks->pluck('id')->all();
        $knownDrinkerIds = $sourceDrinkers->pluck('id')->all();

        $orphanAudits = $sourceAudits->filter(
            fn ($audit) => ! in_array($audit->user, $knownDrinkerIds, true)
                || ($audit->drink !== null && ! in_array($audit->drink, $knownDrinkIds, true))
        )->count();

        $this->components->info(sprintf(
            'Found in mete: %d drinks, %d drinkers, %d transactions (%d reference an unknown drinker/drink and will be skipped).',
            $sourceDrinks->count(),
            $sourceDrinkers->count(),
            $sourceAudits->count(),
            $orphanAudits,
        ));

        if ($dryRun) {
            $this->components->warn('Dry run: no data was written.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($sourceDrinks, $sourceDrinkers, $sourceAudits) {
            $this->importDrinks($sourceDrinks);
            $this->importDrinkers($sourceDrinkers);
            $imported = $this->importTransactions($sourceAudits);

            $this->components->info("Imported {$sourceDrinks->count()} drinks, {$sourceDrinkers->count()} drinkers, and {$imported} transactions.");
        });

        return self::SUCCESS;
    }

    private function importDrinks(LazyCollection|iterable $rows): void
    {
        $now = now();

        $attributes = collect($rows)->map(fn ($row) => [
            'legacy_id' => $row->id,
            'name' => $row->name,
            'price' => $row->price,
            'bottle_size' => $row->bottle_size > 0 ? $row->bottle_size : null,
            'active' => (bool) $row->active,
            'created_at' => $row->created_at ?? $now,
            'updated_at' => $row->updated_at ?? $now,
        ])->all();

        if ($attributes !== []) {
            Drink::upsert($attributes, ['legacy_id'], ['name', 'price', 'bottle_size', 'active']);
        }
    }

    private function importDrinkers(LazyCollection|iterable $rows): void
    {
        $now = now();

        $attributes = collect($rows)->map(fn ($row) => [
            'legacy_id' => $row->id,
            'name' => $row->name,
            'balance' => $row->balance,
            'active' => (bool) $row->active,
            'created_at' => $row->created_at ?? $now,
            'updated_at' => $row->updated_at ?? $now,
        ])->all();

        if ($attributes !== []) {
            Drinker::upsert($attributes, ['legacy_id'], ['name', 'balance', 'active']);
        }
    }

    /**
     * mete's `audits` table has no `type` column and no running balance —
     * it only records a signed `difference` per user, optionally linked to
     * a drink. We classify the type from the sign/drink presence, then
     * back-fill `balance_after` by replaying each drinker's audits newest
     * first starting from their already-imported current balance.
     */
    private function importTransactions(iterable $rows): int
    {
        $drinkLegacyToId = Drink::query()->whereNotNull('legacy_id')->pluck('id', 'legacy_id');
        $drinkerLegacyToId = Drinker::query()->whereNotNull('legacy_id')->pluck('id', 'legacy_id');
        $drinkerBalances = Drinker::query()->whereNotNull('legacy_id')->pluck('balance', 'id');

        $byDrinker = collect($rows)->groupBy('user');

        $rowsToInsert = [];

        foreach ($byDrinker as $legacyDrinkerId => $audits) {
            $drinkerId = $drinkerLegacyToId->get($legacyDrinkerId);

            if ($drinkerId === null) {
                continue;
            }

            $running = (float) $drinkerBalances->get($drinkerId, 0);

            // Newest first, so we can subtract each difference back out to
            // reveal the balance that existed before it was applied.
            $sorted = $audits->sortByDesc(
                fn ($audit) => $audit->created_at.'-'.str_pad((string) $audit->id, 10, '0', STR_PAD_LEFT)
            );

            foreach ($sorted as $audit) {
                $amount = (float) $audit->difference;
                $drinkId = $audit->drink !== null ? $drinkLegacyToId->get($audit->drink) : null;

                $type = match (true) {
                    $audit->drink !== null => TransactionType::Purchase,
                    $amount >= 0 => TransactionType::Deposit,
                    default => TransactionType::Adjustment,
                };

                $rowsToInsert[] = [
                    'legacy_id' => $audit->id,
                    'drinker_id' => $drinkerId,
                    'drink_id' => $drinkId,
                    'type' => $type->value,
                    'amount' => $amount,
                    'balance_after' => $running,
                    'created_by' => null,
                    'created_at' => $audit->created_at,
                    'updated_at' => $audit->created_at,
                ];

                $running -= $amount;
            }
        }

        foreach (array_chunk($rowsToInsert, 500) as $chunk) {
            DB::table('transactions')->upsert(
                $chunk,
                ['legacy_id'],
                ['drinker_id', 'drink_id', 'type', 'amount', 'balance_after', 'created_at', 'updated_at']
            );
        }

        return count($rowsToInsert);
    }
}
