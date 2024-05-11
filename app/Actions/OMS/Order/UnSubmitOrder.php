<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateOrders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrders;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\OMS\Order\OrderStateEnum;
use App\Http\Resources\Sales\OrderResource;
use App\Models\OMS\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class UnSubmitOrder
{
    use WithActionUpdate;
    use AsAction;

    public function handle(Order $order): Order
    {
        $order = $this->update($order, [
            'state' => OrderStateEnum::CREATING
        ]);

        OrganisationHydrateOrders::run(app('currentTenant'));
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
