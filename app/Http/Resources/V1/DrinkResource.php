<?php

namespace App\Http\Resources\V1;

use App\Models\Drink;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Drink
 */
class DrinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $price = (string) ($this->price ?? '0.0');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'bottle_size' => (string) ($this->bottle_size ?? '0.0'),
            'caffeine' => $this->caffeine,
            'price' => $price,
            'active' => $this->active,
            /** @deprecated the v1 specification keeps this as an alias for price */
            'donation_recommendation' => $price,
            'logo_content_type' => $this->image?->mime_type,
            'logo_file_name' => $this->image?->file_name,
            'logo_file_size' => $this->image?->size,
            'logo_updated_at' => $this->image?->updated_at?->toIso8601String(),
            'logo_url' => $this->image?->url(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
