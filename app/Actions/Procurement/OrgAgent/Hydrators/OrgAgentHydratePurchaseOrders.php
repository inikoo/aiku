<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 17:14:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent\Hydrators;

use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\Procurement\OrgAgent;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgAgentHydratePurchaseOrders
{
    use AsAction;
    private OrgAgent $orgAgent;


    public function __construct(OrgAgent $orgAgent)
    {
        $this->orgAgent = $orgAgent;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->orgAgent->id))->dontRelease()];
    }

    public function handle(OrgAgent $orgAgent): void
    {
        $stats = [
            'number_purchase_orders' => $orgAgent->purchaseOrders()->count(),
        ];

        $purchaseOrderStateCounts = $orgAgent->purchaseOrders()
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PurchaseOrderStateEnum::cases() as $productState) {
            $stats['number_purchase_orders_state_'.$productState->snake()] = Arr::get($purchaseOrderStateCounts, $productState->value, 0);
        }

        $purchaseOrderStatusCounts =  $orgAgent->purchaseOrders()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')->all();

        foreach (PurchaseOrderStatusEnum::cases() as $purchaseOrderStatusEnum) {
            $stats['number_purchase_orders_status_'.$purchaseOrderStatusEnum->snake()] = Arr::get($purchaseOrderStatusCounts, $purchaseOrderStatusEnum->value, 0);
        }

        $orgAgent->stats()->update($stats);
    }


}
