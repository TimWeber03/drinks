<?php

namespace App\Models;

use App\Enums\BarcodeType;
use Database\Factories\DrinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drink extends Model
{
    /** @use HasFactory<DrinkFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'bottle_size',
        'caffeine',
        'alcohol',
        'energy',
        'sugar',
        'image_id',
        'active',
        'stock_tracked',
        'stock',
        'legacy_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'bottle_size' => 'decimal:2',
            'caffeine' => 'integer',
            'alcohol' => 'integer',
            'energy' => 'integer',
            'sugar' => 'integer',
            'active' => 'boolean',
            'stock_tracked' => 'boolean',
            'stock' => 'integer',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(Barcode::class, 'linked')->where('type', BarcodeType::Product);
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_tracked && $this->stock <= 0;
    }
}
