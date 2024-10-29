<?php
/*
 * author Arya Permana - Kirin
 * created on 28-10-2024-10h-42m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Enums\Accounting\Invoice;

use App\Enums\EnumHelperTrait;

enum InvoiceCategoryStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS  = 'in_process';
    case ACTIVE   = 'active';
    case CLOSED   = 'closed';
    case COOLDOWN = 'cooldown';

    public static function labels(): array
    {
        return [
            'in_process'      => __('in process'),
            'active'       => __('active'),
            'closed'       => __('closed'),
            'cooldown'       => __('cooldown'),
        ];
    }
}
