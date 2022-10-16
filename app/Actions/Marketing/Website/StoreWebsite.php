<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 18:00:02 Central European Summer Time, Benalmádena, Malaga Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Website;

use App\Actions\Central\CentralDomain\StoreCentralDomain;
use App\Models\Marketing\Shop;
use App\Models\Marketing\Website;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreWebsite
{
    use AsAction;

    public function handle(Shop $shop,array $modelData): Website
    {
        /** @var Website $website */
        $website = $shop->website()->create($modelData);
        $website->stats()->create();
        StoreCentralDomain::run(tenant(),[
            'website_id'=>$website->id,
            'slug'=>$website->code,
            'domain'=>$website->domain
        ]);


        return $website;
    }


}
