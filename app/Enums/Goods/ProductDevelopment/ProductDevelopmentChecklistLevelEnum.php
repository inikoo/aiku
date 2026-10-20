<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-15h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;

enum ProductDevelopmentChecklistLevelEnum: string
{
    use EnumHelperTrait;

    case FAMILY = 'family';
    case SKU    = 'sku';

    public static function labels(): array
    {
        return [
            'family' => __('Family'),
            'sku'    => __('SKU'),
        ];
    }
}
