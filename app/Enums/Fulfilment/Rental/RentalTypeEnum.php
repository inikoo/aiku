<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:56 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\Rental;

use App\Enums\EnumHelperTrait;

enum RentalTypeEnum: string
{
    use EnumHelperTrait;

    case ASSET_LOCATION                           = 'asset_location';
    case LOCATION                                 = 'location';

}
