<?php

namespace App\Models;

use App\Enums\BarcodeType;
use Database\Factories\BarcodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barcode extends Model
{
    /** @use HasFactory<BarcodeFactory> */
    use HasFactory;

    protected $fillable = [
        'barcode',
        'type',
        'linked',
    ];

    protected function casts(): array
    {
        return [
            'type' => BarcodeType::class,
            'linked' => 'integer',
        ];
    }

    /**
     * The drink or drinker this barcode points at, if it still exists.
     */
    public function linkedModel(): Drink|Drinker|null
    {
        return match ($this->type) {
            BarcodeType::Product => Drink::find($this->linked),
            BarcodeType::User => Drinker::find($this->linked),
        };
    }
}
