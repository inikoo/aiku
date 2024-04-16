<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 Apr 2024 17:14:03 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Market\Rental;

use App\Enums\EnumHelperTrait;

enum RentalTypeEnum: string
{
    use EnumHelperTrait;

    case ASSET_LOCATION                           = 'asset_location';
    case LOCATION                                 = 'location';

}
