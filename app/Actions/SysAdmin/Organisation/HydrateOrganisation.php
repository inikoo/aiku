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

class HydrateOrganisation extends HydrateModel
{
    use WithNormalise;


    public function handle(Organisation $organisation): void
    {
        OrganisationHydrateEmployees::run($organisation);
        OrganisationHydrateWarehouse::run($organisation);
        OrganisationHydrateMarket::run($organisation);
        OrganisationHydrateAccounting::run($organisation);
        OrganisationHydrateCustomers::run($organisation);
        OrganisationHydrateOrders::run($organisation);
        OrganisationHydrateProcurement::run($organisation);
        OrganisationHydrateWeb::run($organisation);
    }


    public string $commandSignature = 'hydrate:organisations {organisations?*}';

    public function asCommand(Command $command): int
    {
        $numberOrganisationsHydrated = 0;
        if ($command->argument('organisations')) {
            $organisations = Organisation::whereIn('slug', $command->argument('organisations'))->get();
        } else {
            $organisations = Organisation::all();
        }


        foreach ($organisations as $organisation) {
            $command->info("Hydrating organisation $organisation->name");
            $this->handle($organisation);
            $numberOrganisationsHydrated++;
        }

        if ($numberOrganisationsHydrated === 0) {
            $command->error("No organisations hydrated");

            return 1;
        }

        return 0;
    }
}
