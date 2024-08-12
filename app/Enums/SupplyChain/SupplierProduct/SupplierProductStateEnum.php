<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Aug 2024 16:23:30 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\SupplyChain\SupplierProduct;

use App\Enums\EnumHelperTrait;

enum SupplierProductStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS    = 'in_process';
    case ACTIVE        = 'active';
    case DISCONTINUING = 'discontinuing';
    case DISCONTINUED  = 'discontinued';
}
