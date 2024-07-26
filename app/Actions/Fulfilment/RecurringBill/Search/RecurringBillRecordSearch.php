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
        if ($recurringBill->trashed()) {

            if($recurringBill->universalSearch) {
                $recurringBill->universalSearch()->delete();
            }
            return;
        }

        $result = [
            'route'     => [
                'name'             => 'grp.org.fulfilments.show.crm.customers.show.recurring_bills.show',
                'parameters'       => [
                    'organisation'       => $recurringBill->fulfilmentCustomer->organisation->slug,
                    'fulfilment'         => $recurringBill->fulfilmentCustomer->fulfilment->slug,
                    'fulfilmentCustomer' => $recurringBill->fulfilmentCustomer->slug,
                    'recurringBill'      => $recurringBill->slug,
                ]
            ],
            'container' => [
                'label' => $recurringBill->fulfilment->shop->name,
            ],
            'title'     => $recurringBill->reference,
            'icon'      => [
                'icon' => 'fal fa-receipt',
            ],
            'aaa'       => $recurringBill,
            'meta'      => [
                [
                    'key'       => 'status',
                    'tooltip'   => 'Status',
                    'label'     => $recurringBill->status->labels()[$recurringBill->status->value]
                ],
                [
                    'key'       => 'created_date',
                    'type'      => 'date',
                    'label'     => $recurringBill->created_at,
                    'tooltip'   => 'Created date'
                ],
                [
                    'key'       => 'total',
                    'type'      => 'amount',
                    'code'      => $recurringBill->currency->code,
                    'label'     => 'Total: ',
                    'amount'    => $recurringBill->total_amount,
                    'tooltip'   => 'Total'
                ],
            ],
        ];

        $recurringBill->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $recurringBill->group_id,
                'organisation_id'   => $recurringBill->organisation_id,
                'organisation_slug' => $recurringBill->organisation->slug,
                'fulfilment_id'     => $recurringBill->fulfilment_id,
                'fulfilment_slug'   => $recurringBill->fulfilment->slug,
                'sections'          => ['fulfilment-operations'],
                'haystack_tier_1'   => $recurringBill->reference,
                'result'            => $result
            ]
        );

        $recurringBill->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $recurringBill->group_id,
                'organisation_id'   => $recurringBill->organisation_id,
                'customer_id'       => $recurringBill->fulfilmentCustomer->fulfilment_id,
                'haystack_tier_1'   => $recurringBill->reference,
                'result'            => $result
            ]
        );
    }

}
