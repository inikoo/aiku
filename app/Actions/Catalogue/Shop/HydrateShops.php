<?php

/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 09 Feb 2022 15:04:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Catalogue\Shop;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateAssets;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateDeliveryNotes;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateOutboxes;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateRentals;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateServices;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateSubDepartments;
use App\Actions\HydrateModel;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCollectionCategories;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCollections;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCreditTransactions;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCrmStats;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCustomerBalances;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCustomerInvoices;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCustomers;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateDepartments;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateFamilies;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateInvoiceIntervals;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateInvoices;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateOrders;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydratePaymentAccounts;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydratePayments;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydratePurges;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateSales;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateTopUps;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Collection;

class HydrateShops extends HydrateModel
{
    public string $commandSignature = 'hydrate:shops {organisations?*} {--s|slugs=} ';


    public function handle(Shop $shop): void
    {

        ShopHydratePaymentAccounts::run($shop);
        ShopHydratePayments::run($shop);
        ShopHydrateCustomers::run($shop);
        ShopHydrateCustomerInvoices::run($shop);
        ShopHydrateOrders::run($shop);
        ShopHydratePurges::run($shop);
        ShopHydrateDeliveryNotes::run($shop);
        ShopHydrateDepartments::run($shop);
        ShopHydrateFamilies::run($shop);
        ShopHydrateInvoices::run($shop);
        ShopHydrateSales::run($shop);
        ShopHydrateProducts::run($shop);
        ShopHydrateCollectionCategories::run($shop);
        ShopHydrateCollections::run($shop);
        ShopHydrateAssets::run($shop);
        ShopHydrateProducts::run($shop);
        ShopHydrateServices::run($shop);
        ShopHydrateSubDepartments::run($shop);
        ShopHydrateOutboxes::run($shop);
        ShopHydrateTopUps::run($shop);
        ShopHydrateCreditTransactions::run($shop);
        ShopHydrateCustomerBalances::run($shop);
        ShopHydrateInvoiceIntervals::run($shop);
        ShopHydrateRentals::run($shop);
        ShopHydrateCrmStats::run($shop);

    }


    protected function getModel(string $slug): Shop
    {
        return Shop::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Shop::withTrashed()->get();
    }
}
