<?php

namespace App\Enums\Production\RawMaterial;

use App\Enums\EnumHelperTrait;

enum RawMaterialUnitEnum: string
{
    use EnumHelperTrait;

    case UNIT       = 'unit';
    case PACK       = 'pack';
    case CARTON     = 'carton';
    case LITER      = 'liter';
    case KILOGRAM   = 'kilogram';

}
