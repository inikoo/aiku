<?php
/*
 * author Arya Permana - Kirin
 * created on 04-11-2024-08h-57m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Ordering\PurgedOrder;

use App\Actions\OrgAction;
use App\Enums\Ordering\Purge\PurgedOrderStatusEnum;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\Order;
use App\Models\Ordering\Purge;
use App\Models\Ordering\PurgedOrder;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StorePurgedOrder extends OrgAction
{
    public function handle(Purge $purge, Order $order): PurgedOrder
    {
        data_set($modelData, 'order_id', $order->id);
        data_set($modelData, 'order_last_updated_at', $order->updated_at);
        data_set($modelData, 'amount', $order->net_amount);
        data_set($modelData, 'org_amount', $order->org_net_amount);
        data_set($modelData, 'grp_amount', $order->grp_net_amount);
        data_set($modelData, 'number_transactions', $order->transactions->count());

        $purgedOrder = $purge->purgedOrders()->create($modelData);

        return $purgedOrder;
    }

    public function authorize(ActionRequest $request)
    {
        if($this->asAction)
        {
            return true;
        }
    }

    public function action(Purge $purge, Order $order)
    {
        $this->asAction = true;
        $this->initialisationFromShop($purge->shop, []);
        return $this->handle($purge, $order);
    }
}