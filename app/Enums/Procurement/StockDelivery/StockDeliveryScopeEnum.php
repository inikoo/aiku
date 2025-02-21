<?php
/*
 * author Arya Permana - Kirin
 * created on 21-02-2025-13h-47m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Enums\Procurement\StockDelivery;

use App\Enums\EnumHelperTrait;

enum StockDeliveryScopeEnum: string
{
    use EnumHelperTrait;

    case ORGANISATION   = 'organisation';
    case AGENT = 'agent';
}
