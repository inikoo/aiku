<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-15h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;

enum ProductDevelopmentChecklistItemStateEnum: string
{
    use EnumHelperTrait;

    case MISSING        = 'missing';
    case COMPLETE       = 'complete';
    case NOT_APPLICABLE = 'not_applicable';

    public static function labels(): array
    {
        return [
            'missing'        => __('Missing'),
            'complete'       => __('Complete'),
            'not_applicable' => __('N/A'),
        ];
    }
}
