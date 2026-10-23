<?php

/*
 * author Arya Permana - Kirin
 * created on 09-01-2025-15h-26m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Http\Middleware;

use App\Actions\CRM\TrafficSource\GetTrafficSourceFromUrl;
use App\Actions\Iris\CaptureTrafficSource;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CaptureTrafficSourceMiddleWare
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->isLoggedOutArrivalFromSource($request) || !CaptureTrafficSource::make()->canCaptureTrafficSource()) {
            return $next($request);
        }

        foreach (CaptureTrafficSource::make()->getCookies() as $name => $cookie) {
            Cookie::queue(Cookie::make($name, $cookie['value'], $cookie['duration'], '/', null, null, false));
        }

        return $next($request);
    }

    private function isLoggedOutArrivalFromSource(Request $request): bool
    {
        return $request->isMethod('GET')
            && !$request->ajax()
            && !$request->header('X-Inertia')
            && !$request->expectsJson()
            && GetTrafficSourceFromUrl::run($request->fullUrl()) !== null
            && !auth('retina')->check();
    }
}
