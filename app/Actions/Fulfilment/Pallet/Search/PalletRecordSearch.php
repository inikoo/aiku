<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:39:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\Search;

use App\Models\Fulfilment\Pallet;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Pallet $pallet): void
    {
        if ($pallet->trashed()) {

            if($pallet->universalSearch) {
                $pallet->universalSearch()->delete();
            }
            return;
        }

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

        $pallet->retinaSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $pallet->group_id,
                'organisation_id'   => $pallet->organisation_id,
                'customer_id'       => $pallet->fulfilmentCustomer->customer_id,
                'haystack_tier_1'   => $pallet->reference ?? $pallet->id,
            ]
        );

    }

}
