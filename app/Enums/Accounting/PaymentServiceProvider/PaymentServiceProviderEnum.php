<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 27 Apr 2023 11:53:10 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\Accounting\PaymentServiceProvider;

use App\Enums\EnumHelperTrait;

enum PaymentServiceProviderEnum: string
{
    use EnumHelperTrait;
    case ACCOUNTS                      = 'accounts';
    case CASH                          = 'cash';
    case BANK                          = 'bank';
    case BTREE                         = 'btree';
    case CHECKOUT                      = 'checkout';
    case HOKODO                        = 'hokodo';
    case PAYPAL                        = 'paypal';
    case SOFORT                        = 'sofort';
    case PASTPAY                       = 'pastpay';
    case XENDIT                        = 'xendit';

    public static function labels(): array
    {
        return [
            'accounts'                       => __('Account'),
            'cash'                           => __('Cash'),
            'bank'                           => __('Bank'),
            'btree'                          => __('Btree'),
            'checkout'                       => __('Checkout'),
            'hokodo'                         => __('Hokodo'),
            'paypal'                         => __('Paypal'),
            'sofort'                         => __('Sofort'),
            'xendit'                         => __('Xendit'),
            'pastpay'                        => __('Pastpay')
        ];
    }

    public static function types(): array
    {
        return [
            'accounts'                       => __('account'),
            'cash'                           => __('cash'),
            'bank'                           => __('bank'),
            'btree'                          => __('bank'),
            'checkout'                       => __('electronic_payment_service'),
            'hokodo'                         => __('electronic_payment_service'),
            'paypal'                         => __('electronic_payment_service'),
            'sofort'                         => __('electronic_payment_service'),
            'xendit'                         => __('electronic_payment_service'),
            'pastpay'                        => __('electronic_payment_service')
        ];
    }
}
