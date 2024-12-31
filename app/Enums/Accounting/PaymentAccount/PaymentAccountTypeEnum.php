<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Jan 2024 01:32:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Accounting\PaymentAccount;

use App\Enums\EnumHelperTrait;

enum PaymentAccountTypeEnum: string
{
    use EnumHelperTrait;

    case PAYPAL = 'paypal';
    case WORLD_PAY = 'world_pay';
    case BANK = 'bank';
    case SOFORT = 'sofort';
    case CASH = 'cash';
    case ACCOUNT = 'account';
    case BRAINTREE = 'braintree';
    case BRAINTREE_PAYPAL = 'braintree_paypal';
    case CHECKOUT = 'checkout';
    case HOKODO = 'hokodo';
    case PASTPAY = 'pastpay';
    case CASH_ON_DELIVERY = 'cash_on_delivery';
    case XENDIT = 'xendit';


    public static function labels(): array
    {
        return [
            'world_pay'        => __('World Pay'),
            'paypal'           => __('Paypal'),
            'bank'             => __('Bank'),
            'sofort'           => __('Sofort'),
            'cash'             => __('Cash'),
            'account'          => __('Account'),
            'braintree'        => __('Braintree'),
            'braintree_paypal' => __('Braintree Paypal'),
            'checkout'         => __('Checkout'),
            'hokodo'           => __('Hokodo'),
            'pastpay'          => __('Pastpay'),
            'cash_on_delivery' => __('Cash on Delivery'),
            'xendit'           => __('Xendit'),
        ];
    }

}
