<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 11 Aug 2022 23:03:44 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class EnsureSetupUser
{

    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {

        if(Auth::check()){
            if (! $request->user()->username or  !$request->user()->current_ui_organisation_id) {
                return redirect('setup');
            }

        }

        return $next($request);
    }
}
