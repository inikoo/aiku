<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Jul 2023 09:57:45 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentOrder\Hydrators;

use App\Actions\HydrateModel;
use App\Models\Fulfilment\FulfilmentOrder;
use Illuminate\Support\Collection;

class HydrateFulfilmentOrder extends HydrateModel
{
    public string $commandSignature = 'hydrate:fulfilment-order {organisations?*} {--i|id=}';


    public function handle(FulfilmentOrder $fulfilmentOrder): void
    {
        $this->items($fulfilmentOrder);
    }

    public function originalItems(FulfilmentOrder $fulfilmentOrder): void
    {
        $fulfilmentOrder->stats->update(
            [
                'number_items_at_creation'=> $fulfilmentOrder->items()->count()

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
