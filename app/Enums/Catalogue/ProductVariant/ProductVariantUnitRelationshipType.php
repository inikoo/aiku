<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 16:15:31 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Catalogue\ProductVariant;

use App\Enums\EnumHelperTrait;

enum ProductVariantUnitRelationshipType: string
{
    use EnumHelperTrait;

    case SINGLE                           = 'single';
    case MULTIPLE                         = 'multiple';
    case MULTIPLE_REPACKED                = 'multiple_repacked';
    case MIX                              = 'mix';
}
