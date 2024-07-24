<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:40:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\Search;

use App\Models\Fulfilment\PalletDelivery;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletDeliveryRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(PalletDelivery $palletDelivery): void
    {
        $palletDelivery->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $palletDelivery->group_id,
                'organisation_id'   => $palletDelivery->organisation_id,
                'organisation_slug' => $palletDelivery->organisation->slug,
                'warehouse_id'      => $palletDelivery->warehouse_id,
                'warehouse_slug'    => $palletDelivery->warehouse->slug,
                'fulfilment_id'     => $palletDelivery->fulfilment_id,
                'fulfilment_slug'   => $palletDelivery->fulfilment->slug,
                'sections'          => ['fulfilment'],
                'haystack_tier_1'   => $palletDelivery->reference,
            ]
        );

        $palletDelivery->retinaSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $palletDelivery->group_id,
                'organisation_id'   => $palletDelivery->organisation_id,
                'customer_id'       => $palletDelivery->fulfilmentCustomer->customer_id,
                'haystack_tier_1'   => $palletDelivery->reference,
            ]
        );
    }

}
