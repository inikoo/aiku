<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 09 Sept 2022 03:35:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use App\Models\SysAdmin\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class SetLocale
{

    public function handle(Request $request, Closure $next)
    {
        /** @var User $user */
        if ($user = auth()->user()) {
            $locale = $user->settings['language'];
        } else {
            $locale = Cookie::get('language');

            if (!$locale) {
                $locale = substr(locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2);
            }
        }


        app()->setLocale($locale);

        return $next($request);
    }
}
