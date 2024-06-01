<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Billable\Hydrators;

use App\Models\Catalogue\Billable;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductHydrateUniversalSearch
{
    use AsAction;


    public function handle(Billable $billable): void
    {
        $billable->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $billable->group_id,
                'organisation_id'   => $billable->organisation_id,
                'organisation_slug' => $billable->organisation->slug,
                'shop_id'           => $billable->shop_id,
                'shop_slug'         => $billable->shop->slug,
                'section'           => 'shops',
                'title'             => $billable->name,
                'description'       => $billable->code
            ]
        );
    }

}
