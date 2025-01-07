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

            if ($recurringBill->universalSearch) {
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
            'code'     => [
                'label'   => $recurringBill->reference,
                'tooltip' => __('Reference')
            ],
            'state_icon'         => $recurringBill->status->labels()[$recurringBill->status->value],
            'meta'      => [
                [
                    'type'      => 'number',
                    'label'     => __('Net') . ': ',
                    'number'    => $recurringBill->net_amount,
                    'tooltip'   => __("Net")
                ],
                [
                    'key'       => 'start_date',
                    'type'      => 'date',
                    'label'     => $recurringBill->start_date,
                    'tooltip'   => __('Start date')
                ],
                [
                    'key'       => 'end_date',
                    'type'      => 'date',
                    'label'     => $recurringBill->end_date,
                    'tooltip'   => __('End date')
                ],
                [
                    'key'       => 'total',
                    'type'      => 'currency',
                    'code'      => $recurringBill->currency->code,
                    'label'     => __('Total') . ': ',
                    'amount'    => $recurringBill->total_amount,
                    'tooltip'   => __('Total')
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
                'sections'          => ['fulfilment'],
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
