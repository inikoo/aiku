<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Fulfilment\Pallet\Hydrators;

use App\Models\Fulfilment\Pallet;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Pallet $pallet): void
    {
        $pallet->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $pallet->group_id,
                'organisation_id'   => $pallet->organisation_id,
                'organisation_slug' => $pallet->organisation->slug,
                'fulfilment_id'     => $pallet->fulfilment_id,
                'fulfilment_slug'   => $pallet->fulfilment->slug,
                'warehouse_id'      => $pallet->warehouse_id,
                'warehouse_slug'    => $pallet->warehouse->slug,
                'sections'          => ['fulfilment'],
                'haystack_tier_1'   => $pallet->reference ?? $pallet->id,
            ]
        );
    }

}
