<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 09 Sept 2022 18:04:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsureUserIsNotSetup
{

    public function handle(Request $request, Closure $next): Response|RedirectResponse|JsonResponse
    {


        if ($request->user()->username and $request->user()->number_organisations>0) {
            return redirect('dashboard');
        }
        return $next($request);
    }
}
