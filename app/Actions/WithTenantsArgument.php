<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 11:58:33 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;

trait WithTenantsArgument
{
    protected function getTenants(Command $command): LazyCollection
    {
        return Tenant::query()
            ->when($command->argument('tenants'), function ($query) use ($command) {
                $query->whereIn('code', $command->argument('tenants'));
            })
            ->cursor();
    }
}
