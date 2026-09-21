<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BarcodeType;
use App\Exceptions\OutOfStockException;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\UserResource;
use App\Models\Barcode;
use App\Models\Drink;
use App\Models\Drinker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(Drinker::query()->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(required: true));

        $drinker = Drinker::create($this->attributes($data));

        return UserResource::make($drinker)->response()->setStatusCode(201);
    }

    /**
     * Defaults for creating a new user.
     */
    public function create(): UserResource
    {
        return UserResource::make(new Drinker([
            'name' => '',
            'balance' => 0,
            'active' => true,
            'audit' => false,
            'redirect' => true,
        ]));
    }

    public function show(Drinker $user): UserResource
    {
        return UserResource::make($user);
    }

    public function update(Request $request, Drinker $user): Response
    {
        $data = $request->validate($this->rules(required: false));

        $user->update($this->attributes($data, skipBalance: true));

        if (array_key_exists('balance', $data)) {
            $this->moveBalanceTo($user, (float) $data['balance']);
        }

        return response()->noContent();
    }

    public function destroy(Drinker $user): Response
    {
        $user->delete();

        return response()->noContent();
    }

    /**
     * Statistics across all drinkers.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'user_count' => Drinker::query()->count(),
            'balance_sum' => number_format((float) Drinker::query()->sum('balance'), 2, '.', ''),
        ]);
    }

    /**
     * Amounts are decimal euros in v1, and the mutating calls are GETs.
     */
    public function deposit(Request $request, Drinker $user): Response
    {
        $user->deposit($this->validatedAmount($request));

        return response()->noContent();
    }

    public function payment(Request $request, Drinker $user): Response
    {
        $user->spend($this->validatedAmount($request));

        return response()->noContent();
    }

    public function buy(Request $request, Drinker $user): Response|JsonResponse
    {
        $data = $request->validate(['drink' => ['required', 'integer']]);

        $drink = Drink::find($data['drink']);

        if ($drink === null) {
            return response()->json(['message' => 'Drink not found.'], 404);
        }

        return $this->purchase($user, $drink);
    }

    public function buyByBarcode(Request $request, Drinker $user): Response|JsonResponse
    {
        $value = $this->scalarInput($request, 'barcode');

        Validator::make(['barcode' => $value], ['barcode' => ['required']])->validate();

        $barcode = Barcode::query()
            ->where('barcode', (string) $value)
            ->where('type', BarcodeType::Product)
            ->first();

        $drink = $barcode?->linkedModel();

        if (! $drink instanceof Drink) {
            return response()->json(['message' => 'Barcode not found.'], 404);
        }

        return $this->purchase($user, $drink);
    }

    private function purchase(Drinker $user, Drink $drink): Response|JsonResponse
    {
        try {
            $user->buy($drink);
        } catch (OutOfStockException $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        }

        return response()->noContent();
    }

    private function validatedAmount(Request $request): float
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:0']]);

        return (float) $data['amount'];
    }

    /**
     * Setting a balance directly is recorded like any other balance change so
     * the kiosk history stays in step with the account.
     */
    private function moveBalanceTo(Drinker $user, float $balance): void
    {
        $difference = round($balance - (float) $user->fresh()->balance, 2);

        if ($difference > 0) {
            $user->deposit($difference);
        } elseif ($difference < 0) {
            $user->spend($difference);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(bool $required): array
    {
        return [
            'name' => [$required ? 'required' : 'sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'balance' => ['sometimes', 'numeric'],
            'active' => ['sometimes', 'boolean'],
            'audit' => ['sometimes', 'boolean'],
            'redirect' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data, bool $skipBalance = false): array
    {
        $attributes = array_intersect_key($data, array_flip(['name', 'email', 'active', 'audit', 'redirect']));

        if (! $skipBalance && array_key_exists('balance', $data)) {
            $attributes['balance'] = (float) $data['balance'];
        }

        return $attributes;
    }
}
