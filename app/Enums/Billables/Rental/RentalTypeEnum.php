<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 20 Nov 2024 15:28:32 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Billables\Rental;

use App\Enums\EnumHelperTrait;

enum RentalTypeEnum: string
{
    use EnumHelperTrait;

    case ASSET_LOCATION                           = 'asset_location';
    case LOCATION                                 = 'location';

}
