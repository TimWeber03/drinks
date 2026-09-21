<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Api\ApiController;
use App\Models\Drinker;
use App\Models\Transaction;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AuditController extends ApiController
{
    /**
     * Statistics about previous transactions.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['sometimes', 'nullable', 'date'],
            'user' => ['sometimes', 'nullable', 'integer'],
        ]);

        $drinker = null;

        if (! empty($data['user'])) {
            $drinker = Drinker::find($data['user']);

            if ($drinker === null) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            if (! $drinker->audit) {
                return response()->json(['message' => 'Audits deactivated by user.'], 401);
            }
        }

        $transactions = Transaction::query()
            ->when($drinker, fn ($query) => $query->where('drinker_id', $drinker->id))
            ->where('created_at', '>=', Carbon::parse($data['start'])->startOfDay())
            ->when(
                ! empty($data['end']),
                fn ($query) => $query->where('created_at', '<=', Carbon::parse($data['end'])->endOfDay()),
            )
            ->orderBy('created_at')
            ->get();

        $differences = $transactions->map(fn (Transaction $transaction) => Money::toCents($transaction->amount));

        return response()->json([
            'sum' => $differences->sum(),
            'payments_sum' => $differences->filter(fn (int $cents) => $cents < 0)->sum(),
            'deposits_sum' => $differences->filter(fn (int $cents) => $cents > 0)->sum(),
            'audits' => $transactions->map(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'created_at' => $transaction->created_at?->toIso8601String(),
                'difference' => Money::toCents($transaction->amount),
                'product' => $transaction->drink_id,
            ])->all(),
        ]);
    }
}
