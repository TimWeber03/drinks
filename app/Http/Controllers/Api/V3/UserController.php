<?php

namespace App\Http\Controllers\Api\V3;

use App\Enums\BarcodeType;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\OutOfStockException;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V3\UserResource;
use App\Models\Barcode;
use App\Models\Drink;
use App\Models\Drinker;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends ApiController
{
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(Drinker::query()->with('barcodes')->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules(required: true));

        $drinker = Drinker::create($this->attributes($data) + [
            'balance' => Money::toAmount((int) ($data['balance'] ?? 0)),
        ]);

        /** The specification answers a created user with 200, not 201. */
        return UserResource::make($drinker->load('barcodes'))->response()->setStatusCode(200);
    }

    public function show(Drinker $user): UserResource
    {
        return UserResource::make($user->load('barcodes'));
    }

    public function update(Request $request, Drinker $user): UserResource
    {
        $data = $request->validate($this->rules(required: false));

        $user->update($this->attributes($data));

        if (array_key_exists('balance', $data)) {
            $this->moveBalanceTo($user, (int) $data['balance']);
        }

        return UserResource::make($user->fresh()->load('barcodes'));
    }

    public function destroy(Drinker $user): JsonResponse
    {
        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    /**
     * Statistics across all drinkers.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'user_count' => Drinker::query()->count(),
            'active_count' => Drinker::query()->where('active', true)->count(),
            'balance_sum' => Money::toCents((float) Drinker::query()->sum('balance')),
        ]);
    }

    public function deposit(Request $request, Drinker $user): Response
    {
        $amount = $this->validatedAmount($request, 'amount');

        $user->deposit(Money::toAmount($amount));

        return response()->noContent();
    }

    public function spend(Request $request, Drinker $user): Response
    {
        $amount = $this->validatedAmount($request, 'amount');

        $user->spend(Money::toAmount($amount));

        return response()->noContent();
    }

    public function buy(Request $request, Drinker $user): Response|JsonResponse
    {
        $productId = $this->validatedAmount($request, 'product');

        $drink = Drink::find($productId);

        if ($drink === null) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return $this->purchase($user, $drink);
    }

    public function buyByBarcode(Request $request, Drinker $user): Response|JsonResponse
    {
        $value = $this->scalarInput($request, 'barcode');

        Validator::make(['barcode' => $value], ['barcode' => ['required']])->validate();

        $barcode = Barcode::query()->where('barcode', (string) $value)->first();

        if ($barcode === null) {
            return response()->json(['message' => 'Barcode not found.'], 404);
        }

        if ($barcode->type !== BarcodeType::Product) {
            return response()->json(['message' => 'No product associated to the given barcode.'], 400);
        }

        $drink = $barcode->linkedModel();

        if (! $drink instanceof Drink) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return $this->purchase($user, $drink);
    }

    public function transfer(Request $request, Drinker $user): Response|JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'receiver' => ['required', 'integer', Rule::exists('drinkers', 'id')],
        ]);

        if ((int) $data['receiver'] === $user->id) {
            return response()->json(['message' => 'Sender and receiver must differ.'], 400);
        }

        $receiver = Drinker::findOrFail($data['receiver']);

        try {
            $user->transferTo($receiver, Money::toAmount((int) $data['amount']));
        } catch (InsufficientFundsException $exception) {
            return response()->json(['message' => $exception->getMessage()], 402);
        }

        return response()->noContent();
    }

    /**
     * Look a drinker up by one of their barcodes.
     */
    public function byBarcode(string $barcode): UserResource|JsonResponse
    {
        $record = Barcode::query()->where('barcode', $barcode)->first();

        if ($record === null) {
            return response()->json(['message' => 'Barcode not found.'], 404);
        }

        $drinker = $record->type === BarcodeType::User ? $record->linkedModel() : null;

        if (! $drinker instanceof Drinker) {
            return response()->json(['message' => 'No user associated to the given barcode.'], 400);
        }

        return UserResource::make($drinker->load('barcodes'));
    }

    /**
     * Setting a balance through the API is recorded like any other balance
     * change so the kiosk history stays in step with the account.
     */
    private function moveBalanceTo(Drinker $user, int $cents): void
    {
        $difference = Money::toAmount($cents - Money::toCents($user->fresh()->balance));

        if ($difference > 0) {
            $user->deposit($difference);
        } elseif ($difference < 0) {
            $user->spend($difference);
        }
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

    /**
     * Validate a bare integer body such as an amount in cents or a product id.
     */
    private function validatedAmount(Request $request, string $key): int
    {
        $value = $this->scalarInput($request, $key);

        $validated = Validator::make([$key => $value], [$key => ['required', 'integer', 'min:0']])->validate();

        return (int) $validated[$key];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(bool $required): array
    {
        return [
            'name' => [$required ? 'required' : 'sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'balance' => ['sometimes', 'integer'],
            'active' => ['sometimes', 'boolean'],
            'audit' => ['sometimes', 'boolean'],
            'redirect' => ['sometimes', 'boolean'],
            'avatar' => ['sometimes', 'nullable', 'integer', Rule::exists('images', 'id')],
        ];
    }

    /**
     * Translate the API payload into drinker attributes.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        $attributes = array_intersect_key($data, array_flip(['name', 'email', 'active', 'audit', 'redirect']));

        if (array_key_exists('avatar', $data)) {
            $attributes['avatar_id'] = $data['avatar'];
        }

        return $attributes;
    }
}
