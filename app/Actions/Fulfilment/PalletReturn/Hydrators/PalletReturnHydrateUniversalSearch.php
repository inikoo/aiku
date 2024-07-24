<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Fulfilment\PalletReturn\Hydrators;

use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletReturnHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(PalletReturn $palletReturn): void
    {
        $palletReturn->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $palletReturn->group_id,
                'organisation_id'   => $palletReturn->organisation_id,
                'organisation_slug' => $palletReturn->organisation->slug,
                'warehouse_id'      => $palletReturn->warehouse_id,
                'warehouse_slug'    => $palletReturn->warehouse->slug,
                'fulfilment_id'     => $palletReturn->fulfilment_id,
                'fulfilment_slug'   => $palletReturn->fulfilment->slug,
                'sections'          => ['fulfilment'],
                'haystack_tier_1'   => $palletReturn->reference,
            ]
        );
    }

}
