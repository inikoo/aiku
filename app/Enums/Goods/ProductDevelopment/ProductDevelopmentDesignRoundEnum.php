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

    public function previous(): ?self
    {
        return match ($this) {
            self::CONCEPT => null,
            self::PACKAGING => self::CONCEPT,
            self::LABEL => self::PACKAGING,
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::CONCEPT => self::PACKAGING,
            self::PACKAGING => self::LABEL,
            self::LABEL => null,
        };
    }

    /**
     * The label is designed for each SKU; concept and packaging for the whole family.
     */
    public function isPerSku(): bool
    {
        return $this === self::LABEL;
    }

    public function stage(): ProductDevelopmentStageEnum
    {
        return ProductDevelopmentStageEnum::from($this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::CONCEPT => 'fal fa-lightbulb-on',
            self::PACKAGING => 'fal fa-box-open',
            self::LABEL => 'fal fa-tag',
        };
    }
}
