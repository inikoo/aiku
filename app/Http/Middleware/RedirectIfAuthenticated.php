<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Feb 2024 02:28:35 Mex Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                if($guard=='retina') {
                    $redirectTo='/app/dashboard';
                } else {
                    $redirectTo=RouteServiceProvider::HOME;
                }
                return redirect($redirectTo);
            }
        }

        return $next($request);
    }
}
