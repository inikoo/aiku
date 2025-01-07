<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Retina\Storage\RecurringBill\Search;

use App\Models\Fulfilment\RecurringBill;
use Lorisleiva\Actions\Concerns\AsAction;

class RecurringBillRecordSearch
{
    use AsAction;

    public string $jobQueue = 'retina-search';

    public function handle(RecurringBill $recurringBill): void
    {
        if ($recurringBill->trashed()) {

            if ($recurringBill->retinaSearch) {
                $recurringBill->retinaSearch()->delete();
            }
            return;
        }

        $result = [
            'route'     => [
                'name'             => 'retina.billing.recurring.show',
                'parameters'       => [
                    'recurringBill'      => $recurringBill->slug,
                ]
            ],
            'title'     => $recurringBill->reference,
            'icon'      => [
                'icon' => 'fal fa-receipt',
            ],
            'code'     => [
                'label'   => $recurringBill->reference,
                'tooltip' => __('Reference')
            ],
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

        $recurringBill->retinaSearch()->updateOrCreate(
            [],
            [
            'group_id'          => $recurringBill->group_id,
            'organisation_id'   => $recurringBill->organisation_id,
            'customer_id'       => 462685,
            'haystack_tier_1'   => $recurringBill->reference,
            'result'            => $result,
            ]
        );
    }

}
