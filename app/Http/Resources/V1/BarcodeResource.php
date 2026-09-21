<?php

namespace App\Http\Resources\V1;

use App\Models\Barcode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Barcode
 */
class BarcodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * The v1 barcode is identified by the barcode itself and only ever links
     * to a drink.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->barcode,
            'drink' => $this->linked,
        ];
    }
}
