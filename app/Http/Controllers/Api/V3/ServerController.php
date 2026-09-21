<?php

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;

class ServerController extends ApiController
{
    /**
     * Global server information and capabilities.
     */
    public function info(): JsonResponse
    {
        $creditLimit = config('spacemarket.global_credit_limit');

        return response()->json([[
            'version' => config('spacemarket.version'),
            'global_credit_limit' => is_numeric($creditLimit) ? (int) $creditLimit : false,
            'currency' => config('spacemarket.currency'),
            'currency_before' => (bool) config('spacemarket.currency_before'),
            'decimal_seperator' => config('spacemarket.decimal_separator'),
            'energy' => config('spacemarket.energy'),
            'defaults' => config('spacemarket.defaults'),
        ]]);
    }
}
