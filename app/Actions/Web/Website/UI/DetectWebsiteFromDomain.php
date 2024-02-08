<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Feb 2024 17:10:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\UI;

use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsAction;

class DetectWebsiteFromDomain
{
    use AsAction;

    public function handle($domain): Website
    {
        if(app()->environment('staging')) {
            $domain = str_replace('staging.', '', $domain);
        }
        /** @var Website $website */
        $website= Website::where('domain', $domain)->firstOrFail();
        return $website;
    }

}
