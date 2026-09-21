<?php

namespace App\Http\Resources\V3;

use App\Models\Drinker;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Drinker
 */
class UserResource extends JsonResource
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
            'email' => $this->email,
            'balance' => Money::toCents($this->balance),
            'barcode' => $this->barcodes->first()?->barcode,
            'active' => $this->active,
            'audit' => $this->audit,
            'redirect' => $this->redirect,
            'avatar' => $this->avatar_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
