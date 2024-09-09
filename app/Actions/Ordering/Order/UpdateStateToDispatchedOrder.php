<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\Dropshipping\Shopify\Fulfilment\UpdateFulfilmentShopify;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Ordering\Order;
use Lorisleiva\Actions\ActionRequest;

class UpdateStateToDispatchedOrder extends OrgAction
{
    use WithActionUpdate;
    use HasOrderHydrators;

    public function handle(Order $order): Order
    {
        $data = [
            'state'         => OrderStateEnum::DISPATCHED,
            'dispatched_at' => now()
        ];

        $this->update($order, $data);
        $this->orderHydrators($order);

        // Still dummy data, ignore this
        // UpdateFulfilmentShopify::run($order, [
        //     'company' => 'DHL',
        //     'number'  => 'DHL0001'
        // ]);

        return $order;
    }

    public function action(Order $order): Order
    {
        return $this->handle($order);
    }

    public function asController(Order $order, ActionRequest $request)
    {
        $this->order = $order;
        $this->initialisationFromShop($order->shop, $request);
        return $this->handle($order);
    }
}
