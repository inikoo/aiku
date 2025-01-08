<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Retina\Storage\PalletDelivery\Search;

use App\Models\Fulfilment\PalletDelivery;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletDeliveryRecordSearch
{
    use AsAction;

    public string $jobQueue = 'retina-search';

    public function handle(PalletDelivery $palletDelivery): void
    {

        if ($palletDelivery->trashed()) {

            if ($palletDelivery->retinaSearch) {
                $palletDelivery->retinaSearch()->delete();
            }
            return;
        }

        $result =  [
            'route'     => [
                'name'          => 'retina.storage.pallet-deliveries.show',
                'parameters'    => [
                    'palletDelivery'         => $palletDelivery->slug,
                ]
            ],
            'description'     => [
                'label'   => $palletDelivery->customer_reference
            ],
            'code'         => [
                'label' => $palletDelivery->reference
            ],
            'icon'          => [
                'icon'  => 'fal fa-truck-couch',
            ],
            'state_icon'          => $palletDelivery->state->stateIcon()[$palletDelivery->state->value],
            'meta'          => [
                [
                    'type'      => 'number',
                    'afterLabel'     => __('Pallets') . ': ',
                    'number'    => $palletDelivery->stats->number_pallets,
                    'tooltip'   => __("Pallet's count")
                ],
            ],
        ];


        $palletDelivery->retinaSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $palletDelivery->group_id,
                'organisation_id'   => $palletDelivery->organisation_id,
                'customer_id'       => $palletDelivery->fulfilmentCustomer->customer_id,
                'haystack_tier_1'   => $palletDelivery->reference,
                'result'            => $result,
                'keyword'           => $palletDelivery->slug,
                'keyword_2'         => $palletDelivery->reference
            ]
        );
    }

}
