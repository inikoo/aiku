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

    case PAYPAL    = 'paypal';
    case WORLD_PAY = 'world-pay';
    case BANK      = 'bank';
    case SOFORT    = 'sofort';

    case CASH = 'cash';

    case ACCOUNT = 'account';

    case BRAINTREE = 'braintree';

    case BRAINTREE_PAYPAL = 'braintree-paypal';

    case CHECKOUT = 'checkout';
    case HOKODO   = 'hokodo';

    case PASTPAY          = 'PASTPAY';
    case CASH_ON_DELIVERY = 'cash-on-delivery';

    case XENDIT = 'xendit';


    public static function labels(): array
    {
        return [
            'paypal'           => __('Paypal'),
            'cash'             => __('Cash'),
            'bank'             => __('Bank'),
            'world_pay'        => __('World Pay'),
            'sofort'           => __('Sofort'),
            'account'          => __('Account'),
            'braintree'        => __('Braintree'),
            'braintree_paypal' => __('Braintree Paypal'),
            'checkout'         => __('Checkout'),
            'hokodo'           => __('Hokodo'),
            'PASTPAY'          => __('Pastpay'),
            'xendit'           => __('Xendit'),
        ];
    }

}
