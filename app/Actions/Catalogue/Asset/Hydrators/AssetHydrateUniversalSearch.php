<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Asset\Hydrators;

use App\Models\Catalogue\Asset;
use Lorisleiva\Actions\Concerns\AsAction;

class AssetHydrateUniversalSearch
{
    use AsAction;


    public function handle(Asset $asset): void
    {
        $asset->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $asset->group_id,
                'organisation_id'   => $asset->organisation_id,
                'organisation_slug' => $asset->organisation->slug,
                'shop_id'           => $asset->shop_id,
                'shop_slug'         => $asset->shop->slug,
                'section'           => 'shops',
                'title'             => $asset->name,
                'description'       => $asset->code
            ]
        );
    }

}
