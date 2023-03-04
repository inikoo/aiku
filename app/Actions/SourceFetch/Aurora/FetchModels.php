<?php
/** @noinspection PhpUnused */

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 16:15:47 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\WithTenantsArgument;
use App\Actions\WithTenantSource;
use App\Models\Central\Tenant;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class FetchModels
{
    use AsAction;
    use WithTenantsArgument;
    use WithTenantSource;

    public string $commandSignature = 'fetch:models {tenants?*}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource): void
    {
        FetchShippers::dispatch($tenantSource);
        FetchShops::dispatch($tenantSource);
        FetchEmployees::dispatch($tenantSource);
        Bus::chain([
                       FetchWarehouses::makeJob($tenantSource),
                       FetchWarehouseAreas::makeJob($tenantSource),
                       FetchLocations::makeJob($tenantSource),
                       FetchStocks::makeJob($tenantSource),
                   ])->dispatch();
    }


    public function asCommand(Command $command): int
    {
        $tenants  = $this->getTenants($command);
        $exitCode = 0;

        foreach ($tenants as $tenant) {
            $result = (int)$tenant->execute(
            /**
             * @throws \Exception
             */ function (Tenant $tenant) use ($command) {
                $tenantSource = $this->getTenantSource($tenant);
                $tenantSource->initialisation(app('currentTenant'));

                $this->handle($tenantSource);
            }
            );

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }


}
