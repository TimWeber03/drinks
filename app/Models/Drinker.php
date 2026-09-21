<?php

namespace App\Models;

use App\Enums\BarcodeType;
use App\Enums\TransactionType;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\OutOfStockException;
use App\Support\Money;
use Database\Factories\DrinkerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Drinker extends Model
{
    /** @use HasFactory<DrinkerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'avatar_id',
        'balance',
        'active',
        'audit',
        'redirect',
        'legacy_id',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'active' => 'boolean',
            'audit' => 'boolean',
            'redirect' => 'boolean',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'avatar_id');
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(Barcode::class, 'linked')->where('type', BarcodeType::User);
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
     * Take money off this drinker's balance without tying it to a drink.
     */
    public function spend(float $amount): Transaction
    {
        return $this->applyBalanceChange(
            amount: -abs($amount),
            type: TransactionType::Adjustment,
        );
    }

    /**
     * Manually correct this drinker's balance by a signed amount. The
     * management view is open, so there is not always an admin to credit.
     */
    public function adjustBalance(float $amount, ?User $createdBy = null): Transaction
    {
        return $this->applyBalanceChange(
            amount: $amount,
            type: TransactionType::Adjustment,
            createdBy: $createdBy,
        );
    }

    /**
     * Move money from this drinker to another one, recording both sides.
     *
     * @return Transaction the sending drinker's transaction
     *
     * @throws InsufficientFundsException
     */
    public function transferTo(self $receiver, float $amount): Transaction
    {
        $amount = abs($amount);

        return DB::transaction(function () use ($receiver, $amount) {
            $sender = self::query()->lockForUpdate()->findOrFail($this->id);

            if (! $sender->canAfford($amount)) {
                throw new InsufficientFundsException($this, $amount);
            }

            $receiver->applyBalanceChange(amount: $amount, type: TransactionType::Transfer);

            return $this->applyBalanceChange(amount: -$amount, type: TransactionType::Transfer);
        });
    }

    /**
     * Whether a transfer of this amount stays within the configured credit
     * limit. Transfers, unlike purchases, may not push a drinker past it.
     */
    public function canAfford(float $amount): bool
    {
        $limit = config('spacemarket.global_credit_limit');
        $floor = is_numeric($limit) ? -Money::toAmount((int) $limit) : 0.0;

        return ((float) $this->balance) - $amount >= $floor;
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
