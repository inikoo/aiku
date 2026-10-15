<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-15h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;

enum ProductDevelopmentMemberRoleEnum: string
{
    use EnumHelperTrait;

    case BUYER      = 'buyer';
    case AGENT      = 'agent';
    case PURCHASING = 'purchasing';
    case COMPLIANCE = 'compliance';
    case DESIGNER   = 'designer';
    case WEBMASTER  = 'webmaster';
    case APPROVER   = 'approver';

    public static function labels(): array
    {
        return [
            'buyer'      => __('Buyer'),
            'agent'      => __('Agent'),
            'purchasing' => __('Purchasing'),
            'compliance' => __('Compliance'),
            'designer'   => __('Designer'),
            'webmaster'  => __('Webmaster'),
            'approver'   => __('Approver'),
        ];
    }

    public function statsColumn(): string
    {
        return 'number_required_missing_'.$this->value;
    }
}
