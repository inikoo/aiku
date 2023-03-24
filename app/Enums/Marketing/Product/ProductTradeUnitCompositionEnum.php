<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 03:29:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Marketing\Product;

use App\Enums\EnumHelperTrait;

enum ProductTradeUnitCompositionEnum: string
{
    use EnumHelperTrait;

    case MATCH                 = 'match';
    case MULTIPLE              = 'multiple';
    case MULTIPLE_REPACKED     = 'multiple_repacked';
    case MIX                   = 'mix';
}
