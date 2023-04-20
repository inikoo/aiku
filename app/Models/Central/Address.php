<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Apr 2023 10:14:19 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Central;

use App\Models\Helpers\BaseAddress;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class Address extends BaseAddress
{
    use UsesLandlordConnection;
}
