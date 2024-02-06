<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 17:51:02 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use App\Models\Web\Website;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectWebsite
{
    public function handle(Request $request, Closure $next): Response
    {
        $domain = $request->getHost();
        //todo cache this somehow


        if(app()->environment('staging')) {
            $domain = str_replace('staging.', '', $domain);
        }

        $website = Website::where('domain', $domain)->firstOrFail();

        $request->merge([
            'domain'  => $domain,
            'website' => $website
        ]);

        return $next($request);
    }
}
