<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 May 2023 18:03:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\SupplierTenant;

use App\Enums\EnumHelperTrait;

enum SupplierTenantStatusEnum: string
{
    use EnumHelperTrait;

    case OWNER        = 'owner';
    case ADOPTED      = 'adopted';
    case AVAILABLE    = 'available';


}
