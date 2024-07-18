<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jul 2024 13:21:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RetinaPreparingAccount
{

    public function handle(Request $request, Closure $next)
    {

            if($request->user() && blank($request->user()->customer->fulfilmentCustomer->rentalAgreement)) {
                abort(403, 'We still preparing your account');
            }

        return $next($request);
    }
}
