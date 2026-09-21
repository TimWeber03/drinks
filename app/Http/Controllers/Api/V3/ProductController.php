<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V3\ProductResource;
use App\Models\Drink;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ProductController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(Drink::query()->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(required: true));

        if (Drink::query()->where('name', $data['name'])->exists()) {
            return response()->json(['message' => 'A product with the same name already exists.'], 409);
        }

        $drink = Drink::create($this->attributes($data) + [
            'price' => Money::toAmount($data['price'] ?? (int) config('spacemarket.defaults.price')),
            'active' => $data['active'] ?? (bool) config('spacemarket.defaults.active'),
        ]);

        return ProductResource::make($drink)->response()->setStatusCode(201);
    }

    public function show(Drink $product): ProductResource
    {
        return ProductResource::make($product);
    }

    public function update(Request $request, Drink $product): JsonResponse|ProductResource
    {
        $data = $request->validate($this->rules(required: false));

        if (isset($data['name']) && Drink::query()->where('name', $data['name'])->whereKeyNot($product->id)->exists()) {
            return response()->json(['message' => 'A product with the same name already exists.'], 409);
        }

        $product->update($this->attributes($data));

        return ProductResource::make($product->fresh());
    }

    public function destroy(Drink $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(bool $required): array
    {
        return [
            'name' => [$required ? 'required' : 'sometimes', 'string', 'max:255'],
            'caffeine' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'alcohol' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'energy' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'sugar' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'price' => ['sometimes', 'integer', 'min:0'],
            'active' => ['sometimes', 'boolean'],
            'image' => ['sometimes', 'nullable', 'integer', Rule::exists('images', 'id')],
        ];
    }

    /**
     * Translate the API payload into drink attributes.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        $attributes = array_intersect_key($data, array_flip(['name', 'caffeine', 'alcohol', 'energy', 'sugar', 'active']));

        if (array_key_exists('price', $data)) {
            $attributes['price'] = Money::toAmount((int) $data['price']);
        }

        if (array_key_exists('image', $data)) {
            $attributes['image_id'] = $data['image'];
        }

        return $attributes;
    }
}
