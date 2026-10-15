<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-15h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;

enum ProductDevelopmentStatusEnum: string
{
    use EnumHelperTrait;

    case ACTIVE    = 'active';
    case ON_HOLD   = 'on_hold';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public static function labels(): array
    {
        return [
            'active'    => __('Active'),
            'on_hold'   => __('On hold'),
            'completed' => __('Completed'),
            'cancelled' => __('Cancelled'),
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'active'    => ['tooltip' => __('Active'), 'icon' => 'fal fa-seedling', 'class' => 'text-green-500', 'color' => 'green'],
            'on_hold'   => ['tooltip' => __('On hold'), 'icon' => 'fal fa-pause-circle', 'class' => 'text-amber-500', 'color' => 'amber'],
            'completed' => ['tooltip' => __('Completed'), 'icon' => 'fal fa-check-circle', 'class' => 'text-green-600', 'color' => 'green'],
            'cancelled' => ['tooltip' => __('Cancelled'), 'icon' => 'fal fa-ban', 'class' => 'text-red-500', 'color' => 'red'],
        ];
    }
}
