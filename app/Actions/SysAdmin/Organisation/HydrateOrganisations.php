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
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateLocations;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgAgents;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgSupplierProducts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgSuppliers;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOutboxes;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePalletDeliveries;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePalletReturns;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePallets;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProducts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateRawMaterials;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePaymentAccounts;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePayments;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateCustomers;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateEmployees;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateJobPositions;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateShops;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgPaymentServiceProviders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProductions;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePurchaseOrders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProspects;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateRecurringBills;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateRentals;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateSales;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateServices;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgStocks;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateStoredItemAudits;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateStoredItems;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateSubDepartments;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateSubscription;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWarehouseAreas;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWarehouses;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWebpages;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWebsites;
use App\Actions\Traits\WithNormalise;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;

class HydrateOrganisations extends HydrateModel
{
    use WithNormalise;


    public function handle(Organisation $organisation): void
    {
        OrganisationHydrateEmployees::run($organisation);
        OrganisationHydrateShops::run($organisation);
        OrganisationHydratePayments::run($organisation);
        OrganisationHydratePaymentAccounts::run($organisation);
        OrganisationHydrateOrgPaymentServiceProviders::run($organisation);
        OrganisationHydrateCustomers::run($organisation);
        OrganisationHydrateOrders::run($organisation);
        OrganisationHydratePurchaseOrders::run($organisation);
        OrganisationHydrateWebsites::run($organisation);
        OrganisationHydrateWebpages::run($organisation);
        OrganisationHydrateProspects::run($organisation);
        OrganisationHydrateJobPositions::run($organisation);
        OrganisationHydrateOrgStocks::run($organisation);
        OrganisationHydrateInvoices::run($organisation);
        OrganisationHydrateSales::run($organisation);
        OrganisationHydrateSubscription::run($organisation);
        OrganisationHydrateServices::run($organisation);
        OrganisationHydrateOutboxes::run($organisation);

        if($organisation->type==OrganisationTypeEnum::SHOP) {
            OrganisationHydrateDepartments::run($organisation);
            OrganisationHydrateSubDepartments::run($organisation);
            OrganisationHydrateFamilies::run($organisation);
            OrganisationHydrateCollectionCategories::run($organisation);
            OrganisationHydrateCollections::run($organisation);
            OrganisationHydrateProductions::run($organisation);
            OrganisationHydrateWarehouses::run($organisation);
            OrganisationHydrateWarehouseAreas::run($organisation);
            OrganisationHydrateLocations::run($organisation);
            OrganisationHydrateRawMaterials::run($organisation);
            OrganisationHydrateProducts::run($organisation);
            OrganisationHydrateRentals::run($organisation);

            OrganisationHydrateOrgAgents::run($organisation);
            OrganisationHydrateOrgSuppliers::run($organisation);
            OrganisationHydrateOrgSupplierProducts::run($organisation);

            //fulfilment
            OrganisationHydratePallets::run($organisation);
            OrganisationHydratePalletDeliveries::run($organisation);
            OrganisationHydratePalletReturns::run($organisation);
            OrganisationHydrateStoredItemAudits::run($organisation);
            OrganisationHydrateStoredItems::run($organisation);
            OrganisationHydrateRecurringBills::run($organisation);


        }

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
