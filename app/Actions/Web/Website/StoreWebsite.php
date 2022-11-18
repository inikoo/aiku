<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 11:30:40 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website;

use App\Actions\Central\CentralDomain\StoreCentralDomain;
use App\Models\Marketing\Shop;
use App\Models\Web\Website;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreWebsite
{
    use AsAction;

    public function handle(Shop $shop,array $modelData): Website
    {
        /** @var Website $website */
        $website = $shop->website()->create($modelData);
        $website->stats()->create();
        if($website->state!='closed') {
            StoreCentralDomain::run(tenant(), [
                'website_id' => $website->id,
                'slug'       => $website->code,
                'domain'     => $website->domain
            ]);
        }


        return $website;
    }


}
