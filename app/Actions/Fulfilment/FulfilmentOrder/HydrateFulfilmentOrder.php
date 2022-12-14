<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 28 Nov 2022 12:11:31 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentOrder;

use App\Actions\HydrateModel;
use App\Models\Fulfilment\FulfilmentOrder;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Collection;


class HydrateFulfilmentOrder extends HydrateModel
{

    public string $commandSignature = 'hydrate:fulfilment-order {tenants?*} {--i|id=}';


    public function handle(FulfilmentOrder $fulfilmentOrder): void
    {
        $this->items($fulfilmentOrder);
    }

    public function originalItems(FulfilmentOrder $fulfilmentOrder): void
    {

        $fulfilmentOrder->stats->update(
            [
                'number_items_at_creation'=>$fulfilmentOrder->items()->count()

            ]
        );

    }

    public function items(FulfilmentOrder $fulfilmentOrder): void
    {

        //todo

    }


    protected function getModel(int $id): FulfilmentOrder
    {
        return FulfilmentOrder::find($id);
    }

    protected function getAllModels(): Collection
    {
        return FulfilmentOrder::all();
    }

}


