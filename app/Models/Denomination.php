<?php

namespace App\Models;

use Database\Factories\DenominationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Denomination extends Model
{
    /** @use HasFactory<DenominationFactory> */
    use HasFactory;

    protected $fillable = [
        'amount',
        'image_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }
}
