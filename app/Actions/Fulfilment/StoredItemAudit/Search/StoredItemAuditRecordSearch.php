<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 18:34:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemAudit\Search;

use App\Models\Fulfilment\StoredItemAudit;
use Lorisleiva\Actions\Concerns\AsAction;

class StoredItemAuditRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(StoredItemAudit $storedItemAudit): void
    {

        $storedItemAudit->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $storedItemAudit->group_id,
                'organisation_id'   => $storedItemAudit->organisation_id,
                'organisation_slug' => $storedItemAudit->organisation->slug,
                'warehouse_id'      => $storedItemAudit->warehouse_id,
                'warehouse_slug'    => $storedItemAudit->warehouse->slug,
                'fulfilment_id'     => $storedItemAudit->fulfilment_id,
                'fulfilment_slug'   => $storedItemAudit->fulfilment->slug,
                'sections'          => ['fulfilment'],
                'haystack_tier_1'   => $storedItemAudit->reference,
                'result' => [
                    'route'     => [
                        'name'          => 'grp.org.fulfilments.show.crm.customers.show.stored-item-audits.show',
                        'parameters'    => [
                            'organisation'       => $storedItemAudit->organisation->slug,
                            'fulfilment'         => $storedItemAudit->fulfilment->slug,
                            'fulfilmentCustomer' => $storedItemAudit->fulfilmentCustomer->slug,
                            'storedItemAudit'    => $storedItemAudit->slug
                        ]
                    ],
                    'icon'        => [
                        'icon' => 'fal fa-pallet',
                    ],
                    'code'        => [
                        'label'   => $storedItemAudit->reference,
                        'tooltip' => __('Reference')
                    ],
                    'meta'          => [
                        [
                            'key'       => __("customer_reference"),
                            'label'     => __("Pallet reference (customer's), notes") . ': ' . __($storedItemAudit->customer_reference),
                            'tooltip'   => __("Pallet reference (customer's), notes")
                        ],
                        [
                            'key'       => __("state"),
                            'label'     => __("State") . ': ' . __($storedItemAudit->state->value),
                            'tooltip'   => __("State")
                        ],
                    ],
                ]
            ]
        );

        $storedItemAudit->retinaSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $storedItemAudit->group_id,
                'organisation_id'   => $storedItemAudit->organisation_id,
                'customer_id'       => $storedItemAudit->fulfilmentCustomer->customer_id,
                'haystack_tier_1'   => $storedItemAudit->reference,


            ]
        );
    }

}
