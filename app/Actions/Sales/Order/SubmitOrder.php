<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 05 May 2023 12:02:33 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Order;

use App\Actions\Marketing\Shop\Hydrators\ShopHydrateOrders;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateOrders;
use App\Actions\WithActionUpdate;
use App\Enums\Sales\Order\OrderStateEnum;
use App\Http\Resources\Sales\OrderResource;
use App\Models\Sales\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class SubmitOrder
{
    use WithActionUpdate;
    use AsAction;

    public function handle(Order $order): Order
    {
        $order = $this->update($order, [
            'state' => OrderStateEnum::SUBMITTED
        ]);

        TenantHydrateOrders::run(app('currentTenant'));
        ShopHydrateOrders::run($order->shop);

        return $order;
    }

    public function action(Order $order): Order
    {
        return $this->handle($order);
    }

    public function asController(Order $order): Order
    {
        return $this->handle($order);
    }

    public function jsonResponse(Order $order): OrderResource
    {
        return new OrderResource($order);
    }
}
