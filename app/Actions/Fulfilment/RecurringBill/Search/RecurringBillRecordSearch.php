<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:42:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill\Search;

use App\Models\Fulfilment\RecurringBill;
use Lorisleiva\Actions\Concerns\AsAction;

class RecurringBillRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(RecurringBill $recurringBill): void
    {
        $recurringBill->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $recurringBill->group_id,
                'organisation_id'   => $recurringBill->organisation_id,
                'organisation_slug' => $recurringBill->organisation->slug,
                'fulfilment_id'     => $recurringBill->fulfilment_id,
                'fulfilment_slug'   => $recurringBill->fulfilment->slug,
                'sections'          => ['fulfilment-operations'],
                'haystack_tier_1'   => $recurringBill->reference
            ]
        );

        $recurringBill->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'        => $recurringBill->group_id,
                'organisation_id' => $recurringBill->organisation_id,
                'customer_id'     => $recurringBill->fulfilmentCustomer->fulfilment_id,
                'haystack_tier_1' => $recurringBill->reference
            ]
        );
    }

}
