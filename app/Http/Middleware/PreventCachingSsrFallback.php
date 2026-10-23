<?php

namespace App\Http\Middleware;

use App\Services\ReportingSsrGateway;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventCachingSsrFallback
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->attributes->get(ReportingSsrGateway::FALLBACK_ATTRIBUTE)) {
            $response->headers->set('Cache-Control', 'private, no-store');
            $response->headers->set('X-Aiku-Ssr-Fallback', '1');
        }

        return $response;
    }
}
