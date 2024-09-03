<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 02 Sept 2024 12:51:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Ordering\ShippingZoneSchema;

use App\Enums\EnumHelperTrait;

enum ShippingZoneSchemaTypeEnum: string
{
    use EnumHelperTrait;
    case CURRENT    = 'current';
    case DEAL       = 'deal';
    case IN_RESERVE = 'in-reserve';

}
