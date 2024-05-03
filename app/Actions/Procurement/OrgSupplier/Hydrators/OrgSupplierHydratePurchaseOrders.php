<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 10:42:24 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplier\Hydrators;

use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\Procurement\OrgSupplier;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class OrgSupplierHydratePurchaseOrders
{
    use AsAction;
    private OrgSupplier $orgSupplier;


    public function __construct(OrgSupplier $orgSupplier)
    {
        $this->orgSupplier = $orgSupplier;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->orgSupplier->id))->dontRelease()];
    }

    public function handle(OrgSupplier $orgSupplier): void
    {
        $stats = [
            'number_purchase_orders' => $orgSupplier->purchaseOrders()->count(),
        ];

        $purchaseOrderStateCounts = $orgSupplier->purchaseOrders()
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (PurchaseOrderStateEnum::cases() as $productState) {
            $stats['number_purchase_orders_state_'.$productState->snake()] = Arr::get($purchaseOrderStateCounts, $productState->value, 0);
        }

        $purchaseOrderStatusCounts =  $orgSupplier->purchaseOrders()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')->all();

        foreach (PurchaseOrderStatusEnum::cases() as $purchaseOrderStatusEnum) {
            $stats['number_purchase_orders_status_'.$purchaseOrderStatusEnum->snake()] = Arr::get($purchaseOrderStatusCounts, $purchaseOrderStatusEnum->value, 0);
        }

        $orgSupplier->stats()->update($stats);
    }


}
