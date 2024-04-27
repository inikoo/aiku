<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\HydrateModel;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCollectionCategories;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCollections;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateDepartments;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateFamilies;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateInvoices;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePaymentAccounts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePayments;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCustomers;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateEmployees;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateJobPositions;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateMarket;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgPaymentServiceProviders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProspects;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateRecurringBills;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateSales;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateStocks;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWarehouse;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWeb;
use App\Actions\Traits\WithNormalise;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
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
        OrganisationHydratePayments::run($organisation);
        OrganisationHydratePaymentAccounts::run($organisation);
        OrganisationHydrateOrgPaymentServiceProviders::run($organisation);
        OrganisationHydrateCustomers::run($organisation);
        OrganisationHydrateOrders::run($organisation);
        OrganisationHydrateProcurement::run($organisation);
        OrganisationHydrateWeb::run($organisation);
        OrganisationHydrateProspects::run($organisation);
        OrganisationHydrateJobPositions::run($organisation);
        OrganisationHydrateStocks::run($organisation);
        OrganisationHydrateInvoices::run($organisation);
        OrganisationHydrateSales::run($organisation);


        if($organisation->type==OrganisationTypeEnum::SHOP) {
            OrganisationHydrateDepartments::run($organisation);
            OrganisationHydrateFamilies::run($organisation);
            OrganisationHydrateCollectionCategories::run($organisation);
            OrganisationHydrateCollections::run($organisation);
            OrganisationHydrateRecurringBills::run($organisation);
        }

    }


    public string $commandSignature = 'org:hydrate {organisations?*}';

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
