<?php

namespace App\Http\Controllers\Api\V3;

use App\Enums\BarcodeType;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V3\BarcodeResource;
use App\Models\Barcode;
use App\Models\Drink;
use App\Models\Drinker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class BarcodeController extends ApiController
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'barcode' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(BarcodeType::class)],
            'linked' => ['required', 'integer'],
        ]);

        if (Barcode::query()->where('barcode', $data['barcode'])->exists()) {
            return response()->json(['message' => 'A barcode with this name already exists.'], 409);
        }

        if (! $this->linkedExists(BarcodeType::from($data['type']), (int) $data['linked'])) {
            return response()->json(['message' => 'The linked object does not exist.'], 400);
        }

        $barcode = Barcode::create($data);

        return response()->json([BarcodeResource::make($barcode)->resolve($request)]);
    }

    public function show(Request $request, Barcode $barcode): JsonResponse
    {
        return response()->json([BarcodeResource::make($barcode)->resolve($request)]);
    }

    public function update(Request $request, Barcode $barcode): JsonResponse
    {
        $data = $request->validate([
            'type' => ['sometimes', Rule::enum(BarcodeType::class)],
            'linked' => ['sometimes', 'integer'],
        ]);

        $type = isset($data['type']) ? BarcodeType::from($data['type']) : $barcode->type;
        $linked = (int) ($data['linked'] ?? $barcode->linked);

        if (! $this->linkedExists($type, $linked)) {
            return response()->json(['message' => 'The linked object does not exist.'], 400);
        }

        $barcode->update(['type' => $type, 'linked' => $linked]);

        return response()->json([BarcodeResource::make($barcode->fresh())->resolve($request)]);
    }

    public function destroy(Barcode $barcode): Response
    {
        $barcode->delete();

        return response()->noContent();
    }

    private function linkedExists(BarcodeType $type, int $linked): bool
    {
        return match ($type) {
            BarcodeType::Product => Drink::query()->whereKey($linked)->exists(),
            BarcodeType::User => Drinker::query()->whereKey($linked)->exists(),
        };
    }
}
