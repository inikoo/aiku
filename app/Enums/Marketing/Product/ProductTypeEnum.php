<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 15:47:35 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Marketing\Product;

use App\Enums\EnumHelperTrait;

enum ProductTypeEnum: string
{
    use EnumHelperTrait;

    case PHYSICAL_GOOD    = 'physical_good';
    case SERVICE          = 'service';

}
