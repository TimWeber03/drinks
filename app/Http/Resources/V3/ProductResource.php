<?php

namespace App\Http\Resources\V3;

use App\Models\Drink;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Drink
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'caffeine' => $this->caffeine,
            'alcohol' => $this->alcohol,
            'energy' => $this->energy,
            'sugar' => $this->sugar,
            'price' => Money::toCents($this->price),
            'active' => $this->active,
            'image' => $this->image_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
