<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 09:10
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Enums\SupplyChain\PackingSpec;

use App\Enums\EnumHelperTrait;

enum PackingSpecStatusEnum: string
{
    use EnumHelperTrait;

    case DRAFT      = 'draft';
    case SUBMITTED  = 'submitted';
    case VERIFIED   = 'verified';
    case RETURNED   = 'returned';
    case SUPERSEDED = 'superseded';

    public static function labels(): array
    {
        return [
            'draft'      => __('Draft'),
            'submitted'  => __('Declared'),
            'verified'   => __('Verified'),
            'returned'   => __('Returned to agent'),
            'superseded' => __('Superseded'),
        ];
    }

    /**
     * @return list<self>
     */
    public static function live(): array
    {
        return [self::SUBMITTED, self::VERIFIED, self::RETURNED];
    }

    public static function liveSql(): string
    {
        return implode(', ', array_map(fn (self $status) => "'$status->value'", self::live()));
    }
}
