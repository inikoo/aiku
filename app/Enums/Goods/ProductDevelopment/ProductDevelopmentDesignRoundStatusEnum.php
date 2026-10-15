<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-15h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;

enum ProductDevelopmentDesignRoundStatusEnum: string
{
    use EnumHelperTrait;

    case DRAFT             = 'draft';
    case IN_REVIEW         = 'in_review';
    case CHANGES_REQUESTED = 'changes_requested';
    case APPROVED          = 'approved';

    public static function labels(): array
    {
        return [
            'draft'             => __('Draft'),
            'in_review'         => __('In review'),
            'changes_requested' => __('Changes requested'),
            'approved'          => __('Approved'),
        ];
    }
}
