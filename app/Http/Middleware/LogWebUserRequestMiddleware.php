<?php

/*
 * author Arya Permana - Kirin
 * created on 09-01-2025-15h-26m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogWebUserRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('app.log_user_requests')) {
            return $next($request);
        }

        if (!str_starts_with($request->route()->getName(), 'retina.')) {
            return $next($request);
        }

        if ($request->route()->getName() == 'retina.logout') {
            return $next($request);
        }


        /* @var User $user */
        $user = $request->user();
        if (!app()->runningUnitTests() && $user) {
            $user->stats()->update(['last_active_at' => now()]);
        }

        return $next($request);
    }
}
