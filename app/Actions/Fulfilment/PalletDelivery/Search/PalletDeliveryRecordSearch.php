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

        if ($palletDelivery->trashed()) {

            if($palletDelivery->universalSearch) {
                $palletDelivery->universalSearch()->delete();
            }
            return;
        }

        $result=  [
            'container'     => [
                'key'     => 'warehouse',
                'tooltip' => 'Warehouse',
                'label'   => $palletDelivery->warehouse->name
            ],
            'title'         => $palletDelivery->reference,
            // 'afterTitle'    => [
            //     'label'     => '('.$palletDelivery->customer->reference.')',
            // ],
            'icon'          => [
                'icon'  => 'fal fa-truck-couch',
            ],
            'meta'          => [
                [
                    'key'   => 'label',
                    'label' => $palletDelivery->state->labels()[$palletDelivery->state->value]
                ],
                [
                    'key'       => 'pallets',
                    'type'      => 'number',
                    'label'     => 'Pallets: ',
                    'number'    => $palletDelivery->number_pallets
                ],
                [
                    'key'   => 'created_date',
                    'type'  => 'date',
                    'label' => $palletDelivery->created_at
                ],
            ],
        ];


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
                'result'            => $result
            ]
        );

        $palletDelivery->retinaSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $palletDelivery->group_id,
                'organisation_id'   => $palletDelivery->organisation_id,
                'customer_id'       => $palletDelivery->fulfilmentCustomer->customer_id,
                'haystack_tier_1'   => $palletDelivery->reference,
                'result'            => $result

            ]
        );
    }

}
