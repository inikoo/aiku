<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 20-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Accounting\Payment\Search;

use App\Models\Accounting\Payment;
use Lorisleiva\Actions\Concerns\AsAction;

class PaymentRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Payment $payment): void
    {

        if ($payment->trashed()) {

            if ($payment->universalSearch) {
                $payment->universalSearch()->delete();
            }
            return;
        }

        $customer_id   = null;
        $customer_slug = null;

        $customer = $payment->customer;
        if ($customer) {
            $customer_id   = $customer->id;
            $customer_slug = $customer->slug;
        }

        $modelData = [
            'group_id'          => $payment->group_id,
            'organisation_id'   => $payment->organisation_id,
            'organisation_slug' => $payment->organisation->slug,
            'customer_id'       => $customer_id,
            'customer_slug'     => $customer_slug,
            'sections'          => ['accounting'],
            'haystack_tier_1'   => trim($payment->reference),
            'result'            => [
                'route' => [],
                'code'         => [
                    'label' => $payment->reference,
                ],
                'icon'          => [
                    'icon' => 'fal fa-coins'
                ],
                'meta'          => [
                    [
                        'type'    => 'date',
                        'label'     => $payment->created_at,
                        'tooltip'   => __('Date')
                    ],
                ],

            ]
        ];

        if ($payment->slug != null) {
            $modelData['result'] = array_merge_recursive(
                $modelData['result'],
                [
                    'route' => [
                        'name'       => 'grp.org.accounting.payments.show',
                        'parameters' => [
                            $payment->organisation->slug,
                            $payment->slug,
                        ],
                    ],
                ]
            );
        }

        $payment->universalSearch()->updateOrCreate(
            [],
            $modelData
        );


    }


}
