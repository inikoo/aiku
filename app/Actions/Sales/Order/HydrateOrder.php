<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Dec 2022 15:35:06 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Order;

use App\Actions\HydrateModel;
use App\Models\Sales\Order;
use Illuminate\Support\Collection;


class HydrateOrder extends HydrateModel
{

    public string $commandSignature = 'hydrate:order {tenants?*} {--i|id=}';


    public function handle(Order $order): void
    {
        $this->items($order);
    }

    public function originalItems(Order $order): void
    {

        $order->stats->update(
            [
                'number_items_at_creation'=>$order->transactions()->count()

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


