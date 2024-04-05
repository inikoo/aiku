<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Feb 2024 17:10:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\UI;

use App\Exceptions\IrisWebsiteNotFound;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsAction;

class DetectWebsiteFromDomain
{
    use AsAction;

    /**
     * @throws \App\Exceptions\IrisWebsiteNotFound
     */
    public function handle($domain): Website
    {
        if(app()->environment('local')) {
            $domain = config('app.local.retina_domain');
        }
        if(app()->environment('staging')) {
            $domain = str_replace('canary.', '', $domain);
        }
        // dd($domain);

        /** @var Website $website */
        $website= Website::where('domain', $domain)->first();
        if(!$website) {
            throw IrisWebsiteNotFound::make();
        }
        return $website;
    }

}
