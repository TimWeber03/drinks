<?php

namespace App\Models;

use App\Enums\TransactionType;
use App\Exceptions\OutOfStockException;
use Database\Factories\DrinkerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Drinker extends Model
{
    /** @use HasFactory<DrinkerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'avatar_path',
        'balance',
        'active',
        'legacy_id',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Deduct the price of a drink from this drinker's balance, decrementing
     * the drink's stock first if it's stock-tracked.
     *
     * @throws OutOfStockException
     */
    public function buy(Drink $drink): Transaction
    {
        return DB::transaction(function () use ($drink) {
            $lockedDrink = Drink::query()->lockForUpdate()->findOrFail($drink->id);

            if ($lockedDrink->isOutOfStock()) {
                throw new OutOfStockException($lockedDrink);
            }

            if ($lockedDrink->stock_tracked) {
                $lockedDrink->decrement('stock');
            }

            return $this->applyBalanceChange(
                amount: -$lockedDrink->price,
                type: TransactionType::Purchase,
                drink: $lockedDrink,
            );
        });
    }

    /**
     * Add money to this drinker's balance.
     */
    public function deposit(float $amount, ?User $createdBy = null): Transaction
    {
        return $this->applyBalanceChange(
            amount: abs($amount),
            type: TransactionType::Deposit,
            createdBy: $createdBy,
        );
    }

    /**
     * Manually correct this drinker's balance by a signed amount.
     */
    public function adjustBalance(float $amount, User $createdBy): Transaction
    {
        return $this->applyBalanceChange(
            amount: $amount,
            type: TransactionType::Adjustment,
            createdBy: $createdBy,
        );
    }

    /**
     * All balance mutations funnel through here, locking the row so
     * concurrent kiosk taps can't race each other into a bad balance.
     */
    private function applyBalanceChange(
        float $amount,
        TransactionType $type,
        ?Drink $drink = null,
        ?User $createdBy = null,
    ): Transaction {
        return DB::transaction(function () use ($amount, $type, $drink, $createdBy) {
            $drinker = self::query()->lockForUpdate()->findOrFail($this->id);
            $drinker->balance = $drinker->balance + $amount;
            $drinker->save();

            $transaction = $drinker->transactions()->create([
                'drink_id' => $drink?->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $drinker->balance,
                'created_by' => $createdBy?->id,
            ]);

            $this->setRawAttributes($drinker->getAttributes());

            return $transaction;
        });
    }
}
