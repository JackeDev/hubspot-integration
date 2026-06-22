<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogWebhookRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::channel('webhooks')->info('Webhook attempt', [
            'ip'      => $request->ip(),
            'method'  => $request->method(),
            'payload' => $request->all(),
        ]);

        $response = $next($request);

        Log::channel('webhooks')->info('Webhook response', [
            'status' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
