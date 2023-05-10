<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 10:24:25 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Order\Traits;

use App\Actions\Marketing\Shop\Hydrators\ShopHydrateOrders;
use App\Actions\Sales\Order\HydrateOrder;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateOrders;
use App\Models\Sales\Order;

trait HasHydrators {
    public function orderHydrators(Order $order): void
    {
        HydrateOrder::make()->originalItems($order);
        TenantHydrateOrders::run(app('currentTenant'));

        if($order->customer) {
            $parent = $order->customer;
        } else {
            $parent = $order->customerClient;
        }

        ShopHydrateOrders::run($parent->shop);
    }
}
