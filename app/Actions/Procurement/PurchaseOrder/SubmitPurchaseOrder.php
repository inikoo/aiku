<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\Procurement\Agent\Hydrators\AgentHydratePurchaseOrder;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydratePurchaseOrder;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Actions\WithActionUpdate;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrder;
use Lorisleiva\Actions\Concerns\AsAction;

class SubmitPurchaseOrder
{
    use WithActionUpdate;
    use AsAction;

    public function handle(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $purchaseOrder = $this->update($purchaseOrder, [
            'state' => PurchaseOrderStateEnum::SUBMITTED
        ]);

        $parent = $purchaseOrder->provider;

        if (class_basename($parent) == 'Supplier') {
            SupplierHydratePurchaseOrder::dispatch($parent);
        } else {
            AgentHydratePurchaseOrder::dispatch($parent);
        }

        TenantHydrateProcurement::dispatch(app('currentTenant'));

        return $purchaseOrder;
    }

    public function action(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return $this->handle($purchaseOrder);
    }

    public function asController(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return $this->handle($purchaseOrder);
    }

    public function jsonResponse(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource($purchaseOrder);
    }
}
