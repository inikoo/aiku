<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:11 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order;

use App\Actions\HydrateModel;
use App\Models\OMS\Order;
use Illuminate\Support\Collection;

class HydrateOrder extends HydrateModel
{
    public string $commandSignature = 'hydrate:order {organisations?*} {--i|id=}';


    public function handle(Order $order): void
    {
        $this->items($order);
    }

    public function originalItems(Order $order): void
    {
        $order->stats()->update(
            [
                'number_items_at_creation'=> $order->transactions()->count()

            ]
        );
    }

    public function items(Order $order): void
    {
        //todo
    }


    protected function getModel(int $id): Order
    {
        return Order::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Order::all();
    }
}
