<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 23:37:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNote\Search;

use App\Models\Dispatching\DeliveryNote;
use Lorisleiva\Actions\Concerns\AsAction;

class DeliveryNoteRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(DeliveryNote $deliveryNote): void
    {
        if ($deliveryNote->trashed()) {
            $deliveryNote->universalSearch()->delete();
            return;
        }

        $deliveryNote->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $deliveryNote->group_id,
                'organisation_id'   => $deliveryNote->organisation_id,
                'organisation_slug' => $deliveryNote->organisation->slug,
                'shop_id'           => $deliveryNote->shop_id,
                'shop_slug'         => $deliveryNote->shop->slug,
                'customer_id'       => $deliveryNote->customer_id,
                'customer_slug'     => $deliveryNote->customer->slug,
                'sections'          => ['dispatching'],
                'haystack_tier_1'   => $deliveryNote->reference,
                'haystack_tier_2'   => $deliveryNote->email,
                'result'            => [
                    'aa'            => $deliveryNote,
                    'title'         => $deliveryNote->reference,
                    'icon'          => [
                        'icon' => 'fal fa-truck',
                    ],
                    'meta'      => [
                        [
                            'key'   => 'state',
                            'label' => $deliveryNote->state
                        ],
                        [
                            'key'       => 'created_date',
                            'type'      => 'date',
                            'label'     => $deliveryNote->created_at,
                            'tooltip'   => __('Created at')
                        ],
                        [
                            'key'       => 'updated_date',
                            'type'      => 'date',
                            'label'     => $deliveryNote->updated_at,
                            'tooltip'   => __('Updated at')
                        ],
                    ]
                ]
            ]
        );
    }

}
