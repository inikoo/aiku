<?php

/** @noinspection PhpUnused */

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 16:15:47 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Traits\WithOrganisationsArgument;
use App\Actions\Traits\WithOrganisationSource;
use App\Models\Organisation\Organisation;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchModels
{
    use AsAction;
    use WithOrganisationsArgument;
    use WithOrganisationSource;

    public string $commandSignature = 'fetch:models {tenants?*}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource): void
    {
        FetchShippers::dispatch($organisationSource);
        FetchShops::dispatch($organisationSource);
        FetchEmployees::dispatch($organisationSource);
        Bus::chain([
                       FetchWarehouses::makeJob($organisationSource),
                       FetchWarehouseAreas::makeJob($organisationSource),
                       FetchLocations::makeJob($organisationSource),
                       FetchStocks::makeJob($organisationSource),
                   ])->dispatch();
    }


    public function asCommand(Command $command): int
    {
        $organisations  = $this->getTenants($command);
        $exitCode       = 0;

        foreach ($organisations as $organisation) {
            $result = (int)$organisation->execute(
                /**
                 * @throws \Exception
                 */
                function (Organisation $organisation) use ($command) {
                    $organisationSource = $this->getTenantSource($organisation);
                    $organisationSource->initialisation(app('currentTenant'));

                    $this->handle($organisationSource);
                }
            );

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }
}
