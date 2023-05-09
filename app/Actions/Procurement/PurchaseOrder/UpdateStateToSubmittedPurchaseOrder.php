<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\Procurement\Agent\Hydrators\AgentHydratePurchaseOrders;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydratePurchaseOrders;
use App\Actions\Procurement\PurchaseOrder\Traits\HasHydrators;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Actions\WithActionUpdate;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateStateToSubmittedPurchaseOrder
{
    use WithActionUpdate;
    use AsAction;
    use HasHydrators;

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $data = [
            'state' => PurchaseOrderStateEnum::SUBMITTED
        ];

        if (in_array($purchaseOrder->state, [PurchaseOrderStateEnum::CREATING, PurchaseOrderStateEnum::CONFIRMED])) {
            $purchaseOrder = $this->update($purchaseOrder, $data);
            $purchaseOrder->items()->update($data);

            $parent = $purchaseOrder->provider;

            if (class_basename($parent) == 'Supplier') {
                SupplierHydratePurchaseOrders::dispatch($parent);
            } else {
                AgentHydratePurchaseOrders::dispatch($parent);
            }

            TenantHydrateProcurement::dispatch(app('currentTenant'));

            return $purchaseOrder;
        }

        throw ValidationException::withMessages(['status' => 'You can not change the status to submitted']);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function action(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return $this->handle($purchaseOrder);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function asController(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return $this->handle($purchaseOrder);
    }

    public function jsonResponse(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource($purchaseOrder);
    }
}
