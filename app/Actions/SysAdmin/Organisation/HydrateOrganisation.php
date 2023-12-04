<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\HydrateModel;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateAccounting;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCustomers;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateEmployees;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateMarket;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWarehouse;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWeb;
use App\Actions\Traits\WithNormalise;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class HydrateOrganisation extends HydrateModel
{
    use WithNormalise;

    public string $commandSignature = 'hydrate:tenants {tenants?*}';


    public function handle(): void
    {
        /** @var \App\Models\SysAdmin\Organisation $organisation */
        $organisation = app('currentTenant');
        OrganisationHydrateEmployees::run($organisation);
        OrganisationHydrateWarehouse::run($organisation);
        OrganisationHydrateMarket::run($organisation);
        $this->fulfilmentStats();
        OrganisationHydrateAccounting::run($organisation);
        OrganisationHydrateCustomers::run($organisation);
        OrganisationHydrateOrders::run($organisation);
        OrganisationHydrateProcurement::run($organisation);
        OrganisationHydrateWeb::run($organisation);
    }

    public function fulfilmentStats()
    {
        /** @var \App\Models\SysAdmin\Organisation $organisation */
        $organisation = app('currentTenant');
    }



    protected function getAllModels(): Collection
    {
        return Organisation::all();
    }

    public function asCommand(Command $command): int
    {
        $organisations = $this->getTenants($command);

        $exitCode = 0;

        foreach ($organisations as $organisation) {
            $result = (int)$organisation->execute(function () {
                $this->handle();
            });

            if ($result !== 0) {
                $exitCode = $result;
            }
        }

        return $exitCode;
    }
}
