<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 02 Sept 2024 23:35:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\OrgAction;
use App\Models\Ordering\Order;

class CalculateOrderEstimatedWeight extends OrgAction
{
    public function handle(Order $order): void
    {

        $items       = $order->transactions()->get();
        $estWeight   = 0;
        foreach($items as $item) {
            $weight = $item->historicAsset->model->weight;
            $estWeight += $weight;
        }


        data_set($modelData, 'estimated_weight', $estWeight);

        $order->update($modelData);
    }
}
