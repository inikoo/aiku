<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 13:32:19 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Transaction;

use App\Actions\StoreModelAction;
use App\Models\Utils\ActionResult;
use App\Models\Sales\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreTransaction extends StoreModelAction
{
    use AsAction;

    public function handle(Order $order, array $modelData): ActionResult
    {
        $modelData['organisation_id'] = $order->organisation_id;
        $modelData['shop_id']         = $order->shop_id;
        $modelData['customer_id']     = $order->customer_id;

        $transaction = $order->transactions()->create($modelData);

        return $this->finalise($transaction);
    }
}
