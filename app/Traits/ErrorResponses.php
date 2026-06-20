<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ErrorResponses
{
    public function error(string $message, int $code): JsonResponse
    {
        $status = ($code >= 100 && $code <= 599) ? $code : 500;

        return response()->json([
            "message" => $message,
            "code"    => $code,
        ], $status);
    }
}
