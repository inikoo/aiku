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
use Illuminate\Support\Arr;

class LogUserFirebaseMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && env('LIVE_USERS_LIST')) {
            $organisation = app('currentTenant');

            $route = [
                'icon'      => Arr::get($request->route()->action, 'icon'),
                'label'     => Arr::get($request->route()->action, 'label'),
                'name'      => request()->route()->getName(),
                'arguments' => request()->route()->originalParameters()
            ];
            //  dd($route);

            StoreUserLogFirebase::dispatch($user->username, $organisation->slug, $route);

            if ($request->route()->getName() == 'logout') {
                DeleteUserLogFirebase::dispatch($user, $organisation);
            }
        }

        return $next($request);
    }
}
