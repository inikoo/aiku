<?php

namespace App\Enums\Manufacturing\RawMaterial;

use App\Enums\EnumHelperTrait;

enum RawMaterialTypeEnum: string

{
    use EnumHelperTrait;

    case PART           = 'part';
    case CONSUMABLE     = 'consumable';
    case INTERMEDIATE   = 'intermediate';

}
