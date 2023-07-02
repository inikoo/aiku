<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jul 2023 15:39:43 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Tenancy;

use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class PersonalAccessToken extends \Laravel\Sanctum\PersonalAccessToken
{
    use UsesTenantConnection;
}
