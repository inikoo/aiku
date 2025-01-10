<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 20-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Accounting\PaymentAccount\Search;

use App\Models\Accounting\PaymentAccount;
use Lorisleiva\Actions\Concerns\AsAction;

class PaymentAccountRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(PaymentAccount $paymentAccount): void
    {

        if ($paymentAccount->trashed()) {

            if ($paymentAccount->universalSearch) {
                $paymentAccount->universalSearch()->delete();
            }
            return;
        }

        $modelData = [
            'group_id'          => $paymentAccount->group_id,
            'organisation_id'   => $paymentAccount->organisation_id,
            'organisation_slug' => $paymentAccount->organisation->slug,
            'sections'          => ['accounting'],
            'haystack_tier_1'   => trim($paymentAccount->code.' '.$paymentAccount->name),
            'result'            => [
                'route'     => [
                    'name'          => 'grp.org.accounting.payment-accounts.show',
                    'parameters'    => [
                        $paymentAccount->organisation->slug,
                        $paymentAccount->slug
                    ]
                ],
                'description'     => [
                    'label'   => $paymentAccount->name
                ],
                'code'         => [
                    'label' => $paymentAccount->code,
                ],
                'icon'          => [
                    'icon' => 'fal fa-money-check-alt'
                ],
                'meta'          => [
                    [
                        'label'     => $paymentAccount->paymentServiceProvider->code,
                        'tooltip'   => __('Provider')
                    ],
                    array_merge(
                        (count($paymentAccount->paymentAccountShops) > 0) ?
                    [
                        'label'     => $paymentAccount->paymentAccountShops[0]->name,
                        'tooltip'   => __('Shop')
                    ]
                    : [],
                        [
                        'type'      => 'number',
                        'number'    => $paymentAccount->stats->number_payments,
                        'label'   => __('Payments') . ': '
                    ],
                    )                ],

            ]
        ];

        $paymentAccount->universalSearch()->updateOrCreate(
            [],
            $modelData
        );


    }


}
