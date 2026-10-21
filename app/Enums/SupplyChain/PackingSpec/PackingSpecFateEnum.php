<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 09:10
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Enums\SupplyChain\PackingSpec;

use App\Enums\EnumHelperTrait;

/**
 * What our warehouse does with a packing material once the carton is opened.
 */
enum PackingSpecFateEnum: string
{
    use EnumHelperTrait;

    case PASSED_ON           = 'passed_on';
    case DISCARDED           = 'discarded';
    case REUSED_FOR_DISPATCH = 'reused_for_dispatch';

    public static function labels(): array
    {
        return [
            'passed_on'           => __('Passed on to the customer'),
            'discarded'           => __('Discarded at our warehouse'),
            'reused_for_dispatch' => __('Reused for dispatch'),
        ];
    }

    public function eprActivity(): string
    {
        return match ($this) {
            self::PASSED_ON           => 'imported',
            self::DISCARDED           => 'imported_discarded',
            self::REUSED_FOR_DISPATCH => 'reused',
        };
    }
}
