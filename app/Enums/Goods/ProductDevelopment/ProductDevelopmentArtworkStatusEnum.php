<?php

/*
 * Author Louis Perez
 * Created on 22-10-2026-14h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;

enum ProductDevelopmentArtworkStatusEnum: string
{
    use EnumHelperTrait;

    case NOT_SUBMITTED    = 'not_submitted';
    case IN_REVIEW        = 'in_review';
    case CHANGES_REQUIRED = 'changes_required';
    case APPROVED         = 'approved';

    public static function labels(): array
    {
        return [
            'not_submitted'    => __('Not submitted'),
            'in_review'        => __('In review'),
            'changes_required' => __('Changes required'),
            'approved'         => __('Approved'),
        ];
    }

    public function color(): string
    {
        return match ($this) {
            self::NOT_SUBMITTED => 'gray',
            self::IN_REVIEW => 'blue',
            self::CHANGES_REQUIRED => 'orange',
            self::APPROVED => 'green',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::NOT_SUBMITTED => 'fal fa-circle',
            self::IN_REVIEW => 'fal fa-clock',
            self::CHANGES_REQUIRED => 'fal fa-exclamation-circle',
            self::APPROVED => 'fal fa-check-circle',
        };
    }
}
