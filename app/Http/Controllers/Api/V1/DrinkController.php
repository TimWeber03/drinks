<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\DrinkResource;
use App\Models\Drink;
use App\Models\Image;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DrinkController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return DrinkResource::collection(Drink::query()->with('image')->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(required: true));

        $drink = Drink::create($this->attributes($request, $data) + [
            'price' => Money::toAmount((int) config('spacemarket.defaults.price')),
        ]);

        return DrinkResource::make($drink)->response()->setStatusCode(201);
    }

    /**
     * Defaults for creating a new drink.
     */
    public function create(): DrinkResource
    {
        return DrinkResource::make(new Drink([
            'name' => '',
            'price' => Money::toAmount((int) config('spacemarket.defaults.price')),
            'caffeine' => config('spacemarket.defaults.caffeine'),
            'active' => (bool) config('spacemarket.defaults.active'),
        ]));
    }

    public function show(Drink $drink): DrinkResource
    {
        return DrinkResource::make($drink->load('image'));
    }

    public function update(Request $request, Drink $drink): Response
    {
        $data = $request->validate($this->rules(required: false));

        $drink->update($this->attributes($request, $data, $drink));

        return response()->noContent();
    }

    public function destroy(Drink $drink): Response
    {
        $drink->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(bool $required): array
    {
        return [
            'name' => [$required ? 'required' : 'sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'donation_recommendation' => ['sometimes', 'numeric', 'min:0'],
            'bottle_size' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'caffeine' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'active' => ['sometimes', 'boolean'],
            'logo' => ['sometimes', 'file', 'mimetypes:image/jpeg,image/png,image/gif', 'max:'.(int) config('spacemarket.max_image_kilobytes')],
        ];
    }

    /**
     * Translate the v1 payload into drink attributes. Prices are decimal
     * amounts here rather than the cents v3 speaks.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(Request $request, array $data, ?Drink $drink = null): array
    {
        $attributes = array_intersect_key($data, array_flip(['name', 'bottle_size', 'caffeine', 'active']));

        /** donation_recommendation is the deprecated alias for price. */
        $price = $data['price'] ?? $data['donation_recommendation'] ?? null;

        if ($price !== null) {
            $attributes['price'] = (float) $price;
        }

        if ($request->hasFile('logo')) {
            $drink?->image?->deleteWithFile();

            $attributes['image_id'] = Image::storeUploadedFile($request->file('logo'), 'drinks')->id;
        }

        return $attributes;
    }
}
