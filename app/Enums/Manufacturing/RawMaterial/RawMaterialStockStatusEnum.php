<?php

namespace App\Enums\Manufacturing\RawMaterial;

use App\Enums\EnumHelperTrait;

enum RawMaterialStockStatusEnum: string
{
    use EnumHelperTrait;

    case UNLIMITED      = 'unlimited';
    case SURPLUS        = 'surplus';
    case OPTIMAL        = 'optimal';
    case LOW            = 'low';
    case CRITICAL       = 'critical';
    case OUT_OF_STOCK   = 'out_of_stock';
    case ERROR          = 'error';
}
