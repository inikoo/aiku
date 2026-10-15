<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-15h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;

enum ProductDevelopmentTypeEnum: string
{
    use EnumHelperTrait;

    case NEW        = 'new';
    case REDESIGN   = 'redesign';
    case LABEL_ONLY = 'label_only';

    public static function labels(): array
    {
        return [
            'new'        => __('New'),
            'redesign'   => __('Redesign'),
            'label_only' => __('Label only'),
        ];
    }

    public function icon(): string
    {
        return match ($this) {
            self::NEW => 'fal fa-sparkles',
            self::REDESIGN => 'fal fa-paint-brush',
            self::LABEL_ONLY => 'fal fa-tag',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'teal',
            self::REDESIGN => 'purple',
            self::LABEL_ONLY => 'blue',
        };
    }
}
