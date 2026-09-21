<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AuditController extends ApiController
{
    /**
     * Statistics about previous transactions.
     *
     * v1 passes the range as separate date components, e.g.
     * ?start_date[year]=2016&start_date[month]=1&start_date[day]=1
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'start_date.year' => ['sometimes', 'integer', 'min:1970', 'max:9999'],
            'start_date.month' => ['sometimes', 'integer', 'min:1', 'max:12'],
            'start_date.day' => ['sometimes', 'integer', 'min:1', 'max:31'],
            'end_date.year' => ['sometimes', 'integer', 'min:1970', 'max:9999'],
            'end_date.month' => ['sometimes', 'integer', 'min:1', 'max:12'],
            'end_date.day' => ['sometimes', 'integer', 'min:1', 'max:31'],
        ]);

        $start = $this->date($request, 'start_date', now()->startOfMonth());
        $end = $this->date($request, 'end_date', now());

        $transactions = Transaction::query()
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->orderBy('created_at')
            ->get();

        $differences = $transactions->map(fn (Transaction $transaction) => (float) $transaction->amount);

        return response()->json([
            'sum' => $this->amount($differences->sum()),
            'payments_sum' => $this->amount($differences->filter(fn (float $amount) => $amount < 0)->sum()),
            'deposits_sum' => $this->amount($differences->filter(fn (float $amount) => $amount > 0)->sum()),
            'audits' => $transactions->map(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'created_at' => $transaction->created_at?->toIso8601String(),
                'difference' => (string) $transaction->amount,
                'drink' => $transaction->drink_id,
            ])->all(),
        ]);
    }

    /**
     * Build one end of the range from whichever components were sent, falling
     * back to the matching part of the default date.
     */
    private function date(Request $request, string $key, Carbon $default): Carbon
    {
        return Carbon::create(
            (int) $request->input("{$key}.year", $default->year),
            (int) $request->input("{$key}.month", $default->month),
            (int) $request->input("{$key}.day", $default->day),
        ) ?: $default;
    }

    private function amount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
