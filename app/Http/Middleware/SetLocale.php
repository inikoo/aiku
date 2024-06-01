<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 09 Sept 2022 03:35:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use App\Models\Helpers\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\SysAdmin\User $user */
        if ($user = auth()->user()) {
            $language=Language::find($user->language_id);
            $locale  =$language->code;
        } else {
            $locale = Cookie::get('language');
        }

        if (!$locale) {
            $locale = substr(locale_accept_from_http(Arr::get($_SERVER, 'HTTP_ACCEPT_LANGUAGE', 'en')), 0, 2);
        }


        app()->setLocale($locale);

        return $next($request);
    }
}
