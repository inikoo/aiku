<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jul 2023 13:31:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Models\Tenancy\Tenant;
use Illuminate\Console\Command;

trait WithTenantArgument
{
    protected function getTenant(Command $command): Tenant
    {
        return Tenant::query()->where('code', $command->argument('tenant'))->firstOrFail();
    }
}
