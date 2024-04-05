<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 09 Feb 2022 15:04:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Market\Shop;

use App\Actions\HydrateModel;
use App\Actions\Market\Shop\Hydrators\ShopHydrateCustomerInvoices;
use App\Actions\Market\Shop\Hydrators\ShopHydrateCustomers;
use App\Actions\Market\Shop\Hydrators\ShopHydrateDepartments;
use App\Actions\Market\Shop\Hydrators\ShopHydrateFamilies;
use App\Actions\Market\Shop\Hydrators\ShopHydrateInvoices;
use App\Actions\Market\Shop\Hydrators\ShopHydrateOrders;
use App\Actions\Market\Shop\Hydrators\ShopHydratePaymentAccounts;
use App\Actions\Market\Shop\Hydrators\ShopHydratePayments;
use App\Actions\Market\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\Market\Shop\Hydrators\ShopHydrateSales;
use App\Models\Market\Shop;
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
