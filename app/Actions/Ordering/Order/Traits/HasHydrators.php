<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order\Traits;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateOrders;
use App\Actions\Ordering\Order\HydrateOrder;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrders;
use App\Models\Ordering\Order;

trait HasHydrators
{
    public function orderHydrators(Order $order): void
    {
        HydrateOrder::make()->originalItems($order);
        OrganisationHydrateOrders::run($order->shop->organisation);

        if($order->customer) {
            $parent = $order->customer;
        } else {
            $parent = $order->customerClient;
        }

        ShopHydrateOrders::run($parent->shop);
    }
}
