<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-21h-30m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;

enum ProductDevelopmentFlagEnum: string
{
    use EnumHelperTrait;

    case MISSING = 'missing';
    case OVERDUE = 'overdue';

    public static function labels(): array
    {
        return [
            'missing' => __('Has missing info'),
            'overdue' => __('Overdue'),
        ];
    }

    public function icon(): string
    {
        return match ($this) {
            self::MISSING => 'fal fa-exclamation-triangle',
            self::OVERDUE => 'fal fa-clock',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MISSING => 'amber',
            self::OVERDUE => 'red',
        };
    }
}
