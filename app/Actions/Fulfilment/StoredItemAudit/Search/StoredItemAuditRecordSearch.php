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
