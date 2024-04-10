<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Apr 2024 18:10:11 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Accounting\PaymentServiceProvider;

use App\Enums\EnumHelperTrait;

enum PaymentServiceProviderEnum: string
{
    use EnumHelperTrait;
    case ACCOUNTS                            = 'accounts';
    case CASH                                = 'cash';
    case BANK                                = 'bank';
    case BTREE                               = 'btree';
    case CHECKOUT                            = 'checkout';
    case HOKODO                              = 'hokodo';
    case PAYPAL                              = 'paypal';
    case SOFORT                              = 'sofort';
    case PASTPAY                             = 'pastpay';
    case XENDIT                              = 'xendit';
    case WORLDPAY                            = 'worldpay';
    case COND                                = 'cond';

    public static function labels(): array
    {
        return [
            'accounts'                           => __('Account'),
            'cash'                               => __('Cash'),
            'bank'                               => __('Bank'),
            'btree'                              => __('Btree'),
            'checkout'                           => __('Checkout'),
            'hokodo'                             => __('Hokodo'),
            'paypal'                             => __('Paypal'),
            'sofort'                             => __('Sofort'),
            'xendit'                             => __('Xendit'),
            'pastpay'                            => __('Pastpay'),
            'worldpay'                           => __('Worldpay'),
            'cond'                               => __('Cash on delivery')
        ];
    }

    public static function types(): array
    {
        return [
            'accounts'                           => PaymentServiceProviderTypeEnum::ACCOUNT,
            'cash'                               => PaymentServiceProviderTypeEnum::CASH,
            'bank'                               => PaymentServiceProviderTypeEnum::BANK,
            'btree'                              => PaymentServiceProviderTypeEnum::ELECTRONIC_PAYMENT_SERVICE,
            'checkout'                           => PaymentServiceProviderTypeEnum::ELECTRONIC_PAYMENT_SERVICE,
            'hokodo'                             => PaymentServiceProviderTypeEnum::BUY_NOW_PAY_LATER,
            'paypal'                             => PaymentServiceProviderTypeEnum::ELECTRONIC_PAYMENT_SERVICE,
            'sofort'                             => PaymentServiceProviderTypeEnum::ELECTRONIC_BANKING_E_PAYMENT,
            'xendit'                             => PaymentServiceProviderTypeEnum::ELECTRONIC_PAYMENT_SERVICE,
            'pastpay'                            => PaymentServiceProviderTypeEnum::BUY_NOW_PAY_LATER,
            'worldpay'                           => PaymentServiceProviderTypeEnum::ELECTRONIC_PAYMENT_SERVICE,
            'cond'                               => PaymentServiceProviderTypeEnum::CASH_ON_DELIVERY

        ];
    }
}
