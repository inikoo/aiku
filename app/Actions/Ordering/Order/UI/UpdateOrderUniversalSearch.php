<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 17:46:46 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order\UI;

use App\Actions\HydrateModel;
use App\Actions\Ordering\Order\Hydrators\OrderHydrateUniversalSearch;
use App\Models\Ordering\Order;
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
