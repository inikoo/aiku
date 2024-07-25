<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:38:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\Search;

use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletReturnRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(PalletReturn $palletReturn): void
    {

        if ($palletReturn->trashed()) {

            if($palletReturn->universalSearch) {
                $palletReturn->universalSearch()->delete();
            }
            return;
        }

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

        $palletReturn->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'        => $palletReturn->group_id,
                'organisation_id' => $palletReturn->organisation_id,
                'customer_id'     => $palletReturn->fulfilmentCustomer->customer_id,
                'haystack_tier_1' => $palletReturn->reference,
            ]
        );
    }

}
