<?php
/*
 * author Arya Permana - Kirin
 * created on 18-11-2024-16h-16m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/
namespace App\Actions\Ordering\Purge;

use App\Actions\OrgAction;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\Purge;
use Carbon\Carbon;
use Lorisleiva\Actions\ActionRequest;

class FetchEligiblePurgeOrders extends OrgAction
{
    public function handle(Purge $purge)
    {
        $dateThreshold = Carbon::now()->subDays($purge->inactive_days);
        $orders        = $purge->shop->orders()
            ->where('updated_at', '<', $dateThreshold)
            ->where('state', OrderStateEnum::CREATING)
            ->get();


        return $orders;
    }
}
