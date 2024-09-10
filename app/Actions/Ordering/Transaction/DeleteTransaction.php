<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 23:00:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Transaction;

use App\Actions\Ordering\Order\Hydrators\OrderHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use Lorisleiva\Actions\ActionRequest;

class DeleteTransaction extends OrgAction
{
    use WithActionUpdate;


    private Transaction $transaction;

    public function handle(Order $order, Transaction $transaction): bool
    {

        $transaction->delete();

        OrderHydrateUniversalSearch::dispatch($order);

        return true;
    }

    public function action(Order $order, Transaction $transaction): bool
    {
        $this->initialisationFromShop($order->shop, []);
        return $this->handle($order, $transaction);
    }

    public function asController(Order $order, Transaction $transaction, ActionRequest $request): bool
    {
        $this->initialisationFromShop($order->shop, $request);
        return $this->handle($order, $transaction);
    }
}
