<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 09 Feb 2022 15:04:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Marketing\Shop;

use App\Actions\HydrateModel;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateCustomerInvoices;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateCustomers;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateDepartments;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateInvoices;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateOrders;
use App\Actions\Marketing\Shop\Hydrators\ShopHydratePaymentAccounts;
use App\Actions\Marketing\Shop\Hydrators\ShopHydratePayments;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateProducts;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateSales;
use App\Models\Marketing\Shop;
use Illuminate\Support\Collection;

class HydrateShop extends HydrateModel
{
    public string $commandSignature = 'hydrate:shop {tenants?*} {--i|id=} ';


    public function handle(Shop $shop): void
    {
        ShopHydratePaymentAccounts::run($shop);
        ShopHydratePayments::run($shop);
        ShopHydrateCustomers::run($shop);
        ShopHydrateCustomerInvoices::run($shop);
        ShopHydrateOrders::run($shop);
        ShopHydrateDepartments::run($shop);
        ShopHydrateInvoices::run($shop);
        ShopHydrateSales::run($shop);
        ShopHydrateProducts::run($shop);
    }


    protected function getModel(int $id): Shop
    {
        return Shop::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Shop::withTrashed()->get();
    }
}
