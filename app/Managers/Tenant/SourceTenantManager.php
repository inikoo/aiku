<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 23 Aug 2022 02:49:41 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */


namespace App\Managers\Tenant;

use App\Services\Tenant\SourceTenantService;

interface SourceTenantManager
{
    public function make($name): SourceTenantService;
}
