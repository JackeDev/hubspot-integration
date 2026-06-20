<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait HandleExceptions {

    use ErrorResponses;

    public function handleErrorResponse(mixed $exception, ?string $message = null, ?int $code = null): JsonResponse
    {
        Log::error($exception->getMessage(), ["code" => $exception->getCode()]);
        return $this->error($message ?? $exception->getMessage(), $code ?? (int) $exception->getCode());
    }
}