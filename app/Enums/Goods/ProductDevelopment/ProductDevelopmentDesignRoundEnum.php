<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-15h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;

enum ProductDevelopmentDesignRoundEnum: string
{
    use EnumHelperTrait;

    case CONCEPT   = 'concept';
    case PACKAGING = 'packaging';
    case LABEL     = 'label';

    public static function labels(): array
    {
        return [
            'concept'   => __('Concept'),
            'packaging' => __('Packaging'),
            'label'     => __('Label'),
        ];
    }
}
