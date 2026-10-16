<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-15h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Enums\Goods\ProductDevelopment;

use App\Enums\EnumHelperTrait;
use App\Enums\Goods\TradeUnit\TradeAttachmentScopeEnum;

enum ProductDevelopmentFileScopeEnum: string
{
    use EnumHelperTrait;

    case SDS           = 'sds';
    case IFRA          = 'ifra';
    case CPSR          = 'cpsr';
    case TEST_REPORTS  = 'test_reports';
    case DIELINE       = 'dieline';
    case FACTORY_PHOTO = 'factory_photo';
    case ARTWORK_DRAFT = 'artwork_draft';
    case ARTWORK_FINAL = 'artwork_final';
    case OTHER         = 'other';

    public static function labels(): array
    {
        return [
            'sds'           => 'SDS',
            'ifra'          => 'IFRA',
            'cpsr'          => 'CPSR',
            'test_reports'  => __('Test reports'),
            'dieline'       => __('Dieline'),
            'factory_photo' => __('Factory photo'),
            'artwork_draft' => __('Artwork draft'),
            'artwork_final' => __('Artwork final'),
            'other'         => __('Other'),
        ];
    }

    public function tradeUnitAttachmentScope(): ?TradeAttachmentScopeEnum
    {
        return match ($this) {
            self::SDS => TradeAttachmentScopeEnum::SDS,
            self::IFRA => TradeAttachmentScopeEnum::IFRA,
            self::CPSR => TradeAttachmentScopeEnum::CPSR,
            self::TEST_REPORTS => TradeAttachmentScopeEnum::TEST_REPORTS,
            default => null,
        };
    }

    public function isVersioned(): bool
    {
        return in_array($this, [self::SDS, self::IFRA, self::CPSR, self::DIELINE, self::ARTWORK_FINAL], true);
    }

    public function isPerSku(): bool
    {
        return $this->tradeUnitAttachmentScope() !== null || in_array($this, [self::FACTORY_PHOTO, self::DIELINE], true);
    }

    public function icon(): string
    {
        return match ($this) {
            self::SDS, self::IFRA, self::CPSR => 'fal fa-file-certificate',
            self::TEST_REPORTS => 'fal fa-vial',
            self::DIELINE => 'fal fa-draw-square',
            self::FACTORY_PHOTO => 'fal fa-camera',
            self::ARTWORK_DRAFT, self::ARTWORK_FINAL => 'fal fa-palette',
            self::OTHER => 'fal fa-paperclip',
        };
    }

    /**
     * @return array<int, ProductDevelopmentMemberRoleEnum>
     */
    public function neededBy(): array
    {
        return match ($this) {
            self::SDS, self::IFRA, self::CPSR, self::TEST_REPORTS => [ProductDevelopmentMemberRoleEnum::COMPLIANCE],
            self::FACTORY_PHOTO, self::DIELINE => [ProductDevelopmentMemberRoleEnum::DESIGNER],
            self::ARTWORK_DRAFT, self::ARTWORK_FINAL => [ProductDevelopmentMemberRoleEnum::BUYER, ProductDevelopmentMemberRoleEnum::APPROVER],
            self::OTHER => [],
        };
    }
}
