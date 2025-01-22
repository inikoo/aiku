<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 17:51:02 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use App\Actions\Web\Website\UI\DetectWebsiteFromDomain;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectWebsite
{
    public function handle(Request $request, Closure $next): Response
    {

        $website = DetectWebsiteFromDomain::run($request->getHost());

        if(is_null($website)){
            return redirect()->to('https://'.config('app.domain'));
        }

        $request->merge([
            'domain'  => $website->domain,
            'website' => $website
        ]);

        return $next($request);
    }





}
