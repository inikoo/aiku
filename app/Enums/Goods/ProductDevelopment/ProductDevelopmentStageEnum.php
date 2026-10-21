<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-15h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;

enum ProductDevelopmentStageEnum: string
{
    use EnumHelperTrait;

    case BRIEF               = 'brief';
    case PRODUCT_SPECS       = 'product_specs';
    case COMMERCIAL_TERMS    = 'commercial_terms';
    case PACK_CONTENT        = 'pack_content';
    case CONCEPT             = 'concept';
    case PACKAGING           = 'packaging';
    case LABEL               = 'label';
    case FINAL_APPROVAL      = 'final_approval';
    case ARTWORK_TO_SUPPLIER = 'artwork_to_supplier';

    public static function labels(): array
    {
        return [
            'brief'               => __('Brief'),
            'product_specs'       => __('Product specs'),
            'commercial_terms'    => __('Commercial terms'),
            'pack_content'        => __('Pack content'),
            'concept'             => __('Concept'),
            'packaging'           => __('Packaging'),
            'label'               => __('Label'),
            'final_approval'      => __('Final approval'),
            'artwork_to_supplier' => __('Artwork to supplier'),
        ];
    }

    public function position(): int
    {
        return match ($this) {
            self::BRIEF => 1,
            self::PRODUCT_SPECS, self::COMMERCIAL_TERMS, self::PACK_CONTENT => 2,
            self::CONCEPT => 3,
            self::PACKAGING => 4,
            self::LABEL => 5,
            self::FINAL_APPROVAL => 6,
            self::ARTWORK_TO_SUPPLIER => 7,
        };
    }

    public function subPosition(): ?string
    {
        return match ($this) {
            self::PRODUCT_SPECS => 'a',
            self::COMMERCIAL_TERMS => 'b',
            self::PACK_CONTENT => 'c',
            default => null,
        };
    }

    public function role(): ProductDevelopmentMemberRoleEnum
    {
        return match ($this) {
            self::BRIEF, self::FINAL_APPROVAL => ProductDevelopmentMemberRoleEnum::BUYER,
            self::PRODUCT_SPECS => ProductDevelopmentMemberRoleEnum::AGENT,
            self::COMMERCIAL_TERMS, self::ARTWORK_TO_SUPPLIER => ProductDevelopmentMemberRoleEnum::PURCHASING,
            self::PACK_CONTENT => ProductDevelopmentMemberRoleEnum::COMPLIANCE,
            self::CONCEPT, self::PACKAGING, self::LABEL => ProductDevelopmentMemberRoleEnum::DESIGNER,
        };
    }

    public function defaultDurationDays(): int
    {
        return (int) config('product_development.stage_duration_days.'.$this->value, 0);
    }

    /**
     * @return array<int, string>
     */
    public static function positionLabels(): array
    {
        return [
            1 => __('Brief'),
            2 => __('Specs, terms and pack content'),
            3 => __('Concept'),
            4 => __('Packaging'),
            5 => __('Label'),
            6 => __('Final approval'),
            7 => __('Artwork to supplier'),
            8 => __('Done'),
        ];
    }

    public static function positionLabel(int $position): string
    {
        return self::positionLabels()[$position] ?? (string) $position;
    }

    public static function lastPosition(): int
    {
        return self::ARTWORK_TO_SUPPLIER->position();
    }
}
