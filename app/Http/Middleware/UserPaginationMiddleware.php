<?php
/*
 * author Arya Permana - Kirin
 * created on 31-01-2025-11h-13m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class UserPaginationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            Config::set('ui.table.records_per_page', $request->user()->settings['records_per_page'] ?? 50);
        }

        return $next($request);
    }
}
