<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant;

use App\Actions\HydrateModel;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateAccounting;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateCustomers;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateEmployees;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateGuests;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateInventory;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateMarket;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateOrders;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateUsers;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateWarehouse;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateWeb;
use App\Actions\Traits\WithNormalise;
use App\Models\Tenancy\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class HydrateTenant extends HydrateModel
{
    use WithNormalise;

    public string $commandSignature = 'hydrate:tenants {tenants?*}';


    public function handle(): void
    {
        /** @var \App\Models\Tenancy\Tenant $tenant */
        $tenant = app('currentTenant');
        TenantHydrateEmployees::run($tenant);
        TenantHydrateGuests::run($tenant);
        TenantHydrateWarehouse::run($tenant);
        TenantHydrateInventory::run($tenant);
        TenantHydrateMarket::run($tenant);
        $this->fulfilmentStats();
        TenantHydrateUsers::run($tenant);
        TenantHydrateAccounting::run($tenant);
        TenantHydrateCustomers::run($tenant);
        TenantHydrateOrders::run($tenant);
        TenantHydrateProcurement::run($tenant);
        TenantHydrateWeb::run($tenant);
    }

    public function fulfilmentStats()
    {
        /** @var \App\Models\Tenancy\Tenant $tenant */
        $tenant = app('currentTenant');
    }



    protected function getAllModels(): Collection
    {
        return Tenant::all();
    }

    public function asCommand(Command $command): int
    {
        $tenants = $this->getTenants($command);

        $exitCode = 0;

        foreach ($tenants as $tenant) {
            $result = (int)$tenant->execute(function () {
                $this->handle();
            });

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }
}
