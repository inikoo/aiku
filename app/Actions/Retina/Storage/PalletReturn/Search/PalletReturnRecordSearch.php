<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Retina\Storage\PalletReturn\Search;

use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletReturnRecordSearch
{
    use AsAction;

    public string $jobQueue = 'retina-search';

    public function handle(PalletReturn $palletReturn): void
    {

        if ($palletReturn->trashed()) {

            if ($palletReturn->retinaSearch) {
                $palletReturn->retinaSearch()->delete();
            }
            return;
        }

        $result = [
            'route'     => [
                'name'          => 'retina.fulfilment.storage.pallet-returns.show	',
                'parameters'    => [
                    'palletReturn'           => $palletReturn->slug
                ]
            ],
            'description' => [
                'label'   => $palletReturn->customer_reference
            ],
            'code'         => [
                'label'   => $palletReturn->reference,
                'tooltip' => __('Reference')
            ],
            'icon'          => [
                'icon'  => 'fal fa-truck-couch',
            ],
            'state_icon'         => $palletReturn->state->stateIcon()[$palletReturn->state->value],
            'meta'          => [
                [
                    'label'     => $palletReturn->state->labels()[$palletReturn->type->value],
                    'tooltip'   => __("Type")
                ],
                [
                    'type'      => 'number',
                    'label'     => __('Pallets') . ': ',
                    'number'    => $palletReturn->stats->number_pallets,
                    'tooltip'   => __("Pallets")
                ],
            ],
        ];

        $palletReturn->retinaSearch()->updateOrCreate(
            [],
            [
            'group_id'            => $palletReturn->group_id,
            'organisation_id'     => $palletReturn->organisation_id,
            'customer_id'         => $palletReturn->fulfilmentCustomer->customer_id,
            'haystack_tier_1'     => $palletReturn->reference,
            'result'              => $result,
            'keyword'             => $palletReturn->slug,
            'keyword_2'           => $palletReturn->reference
            ]
        );
    }

}
