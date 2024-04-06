<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 17:46:46 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order\UI;

use App\Actions\HydrateModel;
use App\Actions\OMS\Order\Hydrators\OrderHydrateUniversalSearch;
use App\Models\OMS\Order;
use Illuminate\Support\Collection;

class UpdateOrderUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'order:search {organisations?*} {--s|slugs=}';


    public function handle(Order $order): void
    {
        OrderHydrateUniversalSearch::run($order);
    }


    protected function getModel(string $slug): Order
    {
        return Order::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Order::get();
    }
}
