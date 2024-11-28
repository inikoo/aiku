<?php

namespace App\Enums\Production\RawMaterial;

use App\Enums\EnumHelperTrait;

enum RawMaterialStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS     = 'in_process';
    case IN_USE         = 'in_use';
    case ORPHAN         = 'orphan';
    case DISCONTINUED   = 'discontinued';



}
