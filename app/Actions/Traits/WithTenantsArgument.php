<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jul 2023 13:31:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Models\Tenancy\Tenant;
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
