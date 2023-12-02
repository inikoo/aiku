<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Grouping\Organisation;

use App\Actions\HydrateModel;
use App\Actions\Grouping\Organisation\Hydrators\OrganisationHydrateAccounting;
use App\Actions\Grouping\Organisation\Hydrators\OrganisationHydrateCustomers;
use App\Actions\Grouping\Organisation\Hydrators\OrganisationHydrateEmployees;
use App\Actions\Grouping\Organisation\Hydrators\OrganisationHydrateGuests;
use App\Actions\Grouping\Organisation\Hydrators\OrganisationHydrateMarket;
use App\Actions\Grouping\Organisation\Hydrators\OrganisationHydrateOrders;
use App\Actions\Grouping\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Actions\Grouping\Organisation\Hydrators\OrganisationHydrateUsers;
use App\Actions\Grouping\Organisation\Hydrators\OrganisationHydrateWarehouse;
use App\Actions\Grouping\Organisation\Hydrators\OrganisationHydrateWeb;
use App\Actions\Traits\WithNormalise;
use App\Models\Grouping\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class HydrateOrganisation extends HydrateModel
{
    use WithNormalise;

    public string $commandSignature = 'hydrate:tenants {tenants?*}';


    public function handle(): void
    {
        /** @var \App\Models\Grouping\Organisation $organisation */
        $organisation = app('currentTenant');
        OrganisationHydrateEmployees::run($organisation);
        OrganisationHydrateGuests::run($organisation);
        OrganisationHydrateWarehouse::run($organisation);
        OrganisationHydrateMarket::run($organisation);
        $this->fulfilmentStats();
        OrganisationHydrateUsers::run($organisation);
        OrganisationHydrateAccounting::run($organisation);
        OrganisationHydrateCustomers::run($organisation);
        OrganisationHydrateOrders::run($organisation);
        OrganisationHydrateProcurement::run($organisation);
        OrganisationHydrateWeb::run($organisation);
    }

    public function fulfilmentStats()
    {
        /** @var \App\Models\Grouping\Organisation $organisation */
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
