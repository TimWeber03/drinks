<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    /**
     * Read a single value from the request body.
     *
     * The specification sends bare JSON scalars (e.g. `42`) for endpoints such
     * as deposit or buy, while most clients wrap them in an object keyed by the
     * parameter name. Both spellings, plus form encoded bodies, are accepted.
     */
    protected function scalarInput(Request $request, string $key): mixed
    {
        $decoded = json_decode($request->getContent(), true);

        if (is_scalar($decoded)) {
            return $decoded;
        }

        if (is_array($decoded) && array_key_exists($key, $decoded)) {
            return $decoded[$key];
        }

        return $request->input($key);
    }
}
