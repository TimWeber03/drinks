<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BarcodeType;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\BarcodeResource;
use App\Models\Barcode;
use App\Models\Drink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class BarcodeController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return BarcodeResource::collection(
            Barcode::query()->where('type', BarcodeType::Product)->orderBy('barcode')->get(),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['required', 'string', 'max:255'],
            'drink' => ['required', 'integer'],
        ]);

        if (Barcode::query()->where('barcode', $data['id'])->exists()) {
            return response()->json(['message' => 'A barcode with this name already exists.'], 409);
        }

        if (! Drink::query()->whereKey($data['drink'])->exists()) {
            return response()->json(['message' => 'The linked drink does not exist.'], 400);
        }

        $barcode = Barcode::create([
            'barcode' => $data['id'],
            'type' => BarcodeType::Product,
            'linked' => $data['drink'],
        ]);

        return BarcodeResource::make($barcode)->response()->setStatusCode(201);
    }

    /**
     * Defaults for creating a new barcode.
     */
    public function create(): BarcodeResource
    {
        return BarcodeResource::make(new Barcode(['barcode' => '', 'type' => BarcodeType::Product]));
    }

    /**
     * In v1 a barcode is addressed by the barcode itself, not a numeric id.
     */
    public function destroy(string $barcode): Response|JsonResponse
    {
        $record = Barcode::query()->where('barcode', $barcode)->first();

        if ($record === null) {
            return response()->json(['message' => 'Barcode not found.'], 404);
        }

        $record->delete();

        return response()->noContent();
    }
}
