<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Aug 2024 12:45:17 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Ordering\Order;

use App\Enums\EnumHelperTrait;

enum OrderNestEnum: string
{
    use EnumHelperTrait;

    case BASKET  = 'basket';
    case BACKEND = 'backend';

}
