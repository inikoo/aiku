<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Retina\Storage\Pallet\Search;

use App\Models\Billables\Rental;
use App\Models\Fulfilment\Pallet;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletRecordSearch
{
    use AsAction;

    public string $jobQueue = 'retina-search';

    public function handle(Pallet $pallet): void
    {
        if ($pallet->trashed()) {

            if ($pallet->retinaSearch) {
                $pallet->retinaSearch()->delete();
            }
            return;
        }

        $rental = Rental::find($pallet->rental_id) ?? null;

        $pallet->retinaSearch()->updateOrCreate(
            [],
            [
                'group_id'            => $pallet->group_id,
                'organisation_id'     => $pallet->organisation_id,
                'customer_id'         => $pallet->fulfilmentCustomer->customer_id,
                'haystack_tier_1'     => trim($pallet->reference . ' ' . $rental->name),
                'keyword'             => $pallet->slug,
                'keyword_2'           => $pallet->reference,
                'result'              => [
                    'route'     => [
                        'name'          => 'retina.storage.pallets.show',
                        'parameters'    => [
                            'pallet'             => $pallet->slug
                        ]
                    ],
                    'icon'        => [
                        'icon' => 'fal fa-pallet',
                    ],
                    'code'        => [
                        'label'   => $pallet->reference,
                        'tooltip' => __('Reference')
                    ],
                    'description' => [
                        'label' => $rental->name
                    ],
                    'state_icon'          => $pallet->type->typeIcon()[$pallet->type->value],
                    'meta'          => [
                        [
                            'key'       => __("customer_reference"),
                            'label'     => __("Pallet reference (customer's), notes") . ': ' . __($pallet->customer_reference),
                            'tooltip'   => __("Pallet reference (customer's), notes")
                        ],
                        [
                            'key'       => __("state"),
                            'icon'      => $pallet->state->stateIcon()[$pallet->state->value],
                            'label'     => __("State") . ': ' . __($pallet->state->value),
                            'tooltip'   => __("State")
                        ],
                        [
                            'key'       => __("status"),
                            'icon'      => $pallet->status->statusIcon()[$pallet->status->value],
                            'label'     => __("Status") . ': ' . __($pallet->status->value),
                            'tooltip'   => __("Status")
                        ],
                    ],
                ]
            ]
        );
    }

}
