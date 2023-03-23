<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 04:16:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\SupplierProduct;

use App\Enums\EnumHelperTrait;

enum SupplierProductTradeUnitCompositionEnum: string
{
    use EnumHelperTrait;

    case MATCH                 = 'match';
    case MULTIPLE              = 'multiple';
    case MULTIPLE_REPACKED     = 'multiple_repacked';
    case MIX                   = 'mix';
}
