<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 12:40:55 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\Supplier;

use App\Enums\EnumHelperTrait;

enum SupplierTypeEnum: string
{
    use EnumHelperTrait;

    case SUPPLIER     = 'supplier';
    case SUB_SUPPLIER = 'sub-supplier';


}
