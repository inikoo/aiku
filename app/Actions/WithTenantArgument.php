<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 11:58:33 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;

trait WithTenantArgument
{
    protected function getTenant(Command $command): Tenant
    {
        return Tenant::query()->where('slug', $command->argument('tenant'))->firstOrFail();
    }
}
