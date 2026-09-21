<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V3\DenominationResource;
use App\Models\Denomination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class DenominationController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return DenominationResource::collection(Denomination::query()->orderBy('amount')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'image' => ['sometimes', 'nullable', 'integer', Rule::exists('images', 'id')],
        ]);

        if (Denomination::query()->where('amount', $data['amount'])->exists()) {
            return response()->json(['message' => 'A denomination with this amount already exists.'], 409);
        }

        $denomination = Denomination::create([
            'amount' => $data['amount'],
            'image_id' => $data['image'] ?? null,
        ]);

        return response()->json([DenominationResource::make($denomination)->resolve($request)]);
    }

    public function show(Request $request, Denomination $denomination): JsonResponse
    {
        return response()->json([DenominationResource::make($denomination)->resolve($request)]);
    }

    public function update(Request $request, Denomination $denomination): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['sometimes', 'integer', 'min:1'],
            'image' => ['sometimes', 'nullable', 'integer', Rule::exists('images', 'id')],
        ]);

        if (isset($data['amount']) && Denomination::query()->where('amount', $data['amount'])->whereKeyNot($denomination->id)->exists()) {
            return response()->json(['message' => 'A denomination with this amount already exists.'], 409);
        }

        $attributes = array_intersect_key($data, array_flip(['amount']));

        if (array_key_exists('image', $data)) {
            $attributes['image_id'] = $data['image'];
        }

        $denomination->update($attributes);

        return response()->json([DenominationResource::make($denomination->fresh())->resolve($request)]);
    }

    public function destroy(Denomination $denomination): Response
    {
        $denomination->delete();

        return response()->noContent();
    }
}
