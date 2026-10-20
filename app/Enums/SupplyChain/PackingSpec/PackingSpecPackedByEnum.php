<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 09:10
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Enums\SupplyChain\PackingSpec;

use App\Enums\EnumHelperTrait;

enum PackingSpecPackedByEnum: string
{
    use EnumHelperTrait;

    case AGENT       = 'agent';
    case SUPPLIER    = 'supplier';
    case HUB         = 'hub';
    case OWN_FACTORY = 'own_factory';

    public static function labels(): array
    {
        return [
            'agent'       => __('Agent'),
            'supplier'    => __('Supplier'),
            'hub'         => __('Hub'),
            'own_factory' => __('Own factory'),
        ];
    }
}
