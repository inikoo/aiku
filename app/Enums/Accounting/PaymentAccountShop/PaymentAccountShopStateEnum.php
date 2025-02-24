<?php

/*
 * author Arya Permana - Kirin
 * created on 17-02-2025-15h-23m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Enums\Accounting\PaymentAccountShop;

use App\Enums\EnumHelperTrait;

enum PaymentAccountShopStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS = 'in_process';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';


    public static function labels(): array
    {
        return [
            'in_process' => __('In Process'),
            'active'     => __('Active'),
            'inactive'   => __('Inactive'),
        ];
    }

}
