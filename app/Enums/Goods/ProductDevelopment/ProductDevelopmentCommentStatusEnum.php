<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-15h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;

enum ProductDevelopmentCommentStatusEnum: string
{
    use EnumHelperTrait;

    case OPEN     = 'open';
    case RESOLVED = 'resolved';

    public static function labels(): array
    {
        return [
            'open'     => __('Open'),
            'resolved' => __('Resolved'),
        ];
    }
}
