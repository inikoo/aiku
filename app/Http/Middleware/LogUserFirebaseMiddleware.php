<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 21 Aug 2023 19:53:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use App\Actions\Firebase\DeleteUserLogFirebase;
use App\Actions\Firebase\StoreUserLogFirebase;
use Closure;
use Illuminate\Http\Request;

class LogUserFirebaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user   = $request->user();

        if($user && env('LIVE_USERS_LIST')) {
            $tenant = app('currentTenant');

            $route = [
                'module'    => explode('.', request()->route()->getName())[0],
                'name'      => request()->route()->getName(),
                'arguments' => request()->route()->originalParameters()
            ];

            StoreUserLogFirebase::dispatch($user, $tenant, $route);

            if ($request->route()->getName() == 'logout') {
                DeleteUserLogFirebase::dispatch($user, $tenant);
            }

        }

        return $next($request);
    }
}
