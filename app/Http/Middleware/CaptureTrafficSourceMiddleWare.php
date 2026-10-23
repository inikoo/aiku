<?php

/*
 * author Arya Permana - Kirin
 * created on 09-01-2025-15h-26m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Http\Middleware;

use App\Actions\Iris\CaptureTrafficSource;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CaptureTrafficSourceMiddleWare
{
    /**
     * Captures the ad click of a visitor landing on an /app/ page while logged out (registration form,
     * register, basket). Those pages bypass Varnish, and the /app/ first-hit fetch that captures for a
     * logged-in customer needs a login, so without this their clicks were never recorded.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->isLoggedOutPageLoad($request) || !CaptureTrafficSource::make()->canCaptureTrafficSource()) {
            return $next($request);
        }

        if (!$request->headers->has('X-Original-Referer')) {
            $request->headers->set('X-Original-Referer', (string) $request->headers->get('referer', ''));
        }

        foreach (CaptureTrafficSource::make()->getCookies() as $name => $cookie) {
            Cookie::queue(Cookie::make($name, $cookie['value'], $cookie['duration'], '/', null, null, false));
        }

        return $next($request);
    }

    private function isLoggedOutPageLoad(Request $request): bool
    {
        return $request->isMethod('GET')
            && !$request->ajax()
            && !$request->header('X-Inertia')
            && !$request->expectsJson()
            && !auth('retina')->check();
    }
}
