<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\Hydrators;

use App\Models\Accounting\Payment;
use Lorisleiva\Actions\Concerns\AsAction;

class PaymentHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Payment $payment): void
    {
        $customer_id   = null;
        $customer_slug = null;

        $customer = $payment->customer;
        if ($customer) {
            $customer_id   = $customer->id;
            $customer_slug = $customer->slug;
        }

        $payment->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $payment->group_id,
                'organisation_id'   => $payment->organisation_id,
                'organisation_slug' => $payment->organisation->slug,
                'shop_id'           => $payment->shop_id,
                'shop_slug'         => $payment->shop->slug,
                'customer_id'       => $customer_id,
                'customer_slug'     => $customer_slug,
                'section'           => 'accounting',
                'title'             => $payment->reference,
            ]
        );
    }

}
