<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jul 2024 20:52:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill\Hydrators;

use App\Models\Fulfilment\RecurringBill;
use Lorisleiva\Actions\Concerns\AsAction;

class RecurringBillHydrateUniversalSearch
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
    }

}
