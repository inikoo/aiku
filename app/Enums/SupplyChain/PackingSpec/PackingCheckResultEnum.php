<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 09:10
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Enums\SupplyChain\PackingSpec;

use App\Enums\EnumHelperTrait;

enum PackingCheckResultEnum: string
{
    use EnumHelperTrait;

    case PENDING  = 'pending';
    case PASSED   = 'passed';
    case MISMATCH = 'mismatch';

    public static function labels(): array
    {
        return [
            'pending'  => __('To check'),
            'passed'   => __('Matches the spec'),
            'mismatch' => __('Does not match'),
        ];
    }
}
