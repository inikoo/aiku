<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-15h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;

enum ProductDevelopmentStageStateEnum: string
{
    use EnumHelperTrait;

    case PENDING     = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED   = 'completed';
    case SKIPPED     = 'skipped';

    public static function labels(): array
    {
        return [
            'pending'     => __('Pending'),
            'in_progress' => __('In progress'),
            'completed'   => __('Completed'),
            'skipped'     => __('Skipped'),
        ];
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::COMPLETED, self::SKIPPED], true);
    }
}
