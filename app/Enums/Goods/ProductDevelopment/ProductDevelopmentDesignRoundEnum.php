<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-15h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;

/**
 * Only the concept still runs as versioned rounds; packaging and label became design sections with one
 * artwork per SKU (HELP-4211). Their labels stay so older activity still reads.
 */
enum ProductDevelopmentDesignRoundEnum: string
{
    use EnumHelperTrait;

    case CONCEPT = 'concept';

    public static function labels(): array
    {
        return [
            'concept'   => __('Concept'),
            'packaging' => __('Packaging'),
            'label'     => __('Label'),
        ];
    }

    public function stage(): ProductDevelopmentStageEnum
    {
        return ProductDevelopmentStageEnum::from($this->value);
    }

    public function icon(): string
    {
        return 'fal fa-lightbulb-on';
    }
}
