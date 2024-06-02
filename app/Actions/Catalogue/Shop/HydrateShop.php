<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 09 Feb 2022 15:04:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Catalogue\Shop;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateAssets;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateServices;
use App\Actions\HydrateModel;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCollectionCategories;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCollections;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCustomerInvoices;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateCustomers;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateDepartments;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateFamilies;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateInvoices;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateOrders;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydratePaymentAccounts;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydratePayments;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateSales;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Collection;

class HydrateShop extends HydrateModel
{
    public string $commandSignature = 'shop:hydrate {organisations?*} {--s|slugs=} ';


    public function handle(Shop $shop): void
    {

        ShopHydratePaymentAccounts::run($shop);
        ShopHydratePayments::run($shop);
        ShopHydrateCustomers::run($shop);
        ShopHydrateCustomerInvoices::run($shop);
        ShopHydrateOrders::run($shop);
        ShopHydrateDepartments::run($shop);
        ShopHydrateFamilies::run($shop);
        //ShopHydrateInvoices::run($shop);
        ShopHydrateSales::run($shop);
        ShopHydrateProducts::run($shop);
        ShopHydrateCollectionCategories::run($shop);
        ShopHydrateCollections::run($shop);
        ShopHydrateAssets::run($shop);
        ShopHydrateProducts::run($shop);
        ShopHydrateServices::run($shop);

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
