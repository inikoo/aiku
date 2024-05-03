<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\Procurement\OrgAgent\Hydrators\OrgAgentHydratePurchaseOrders;
use App\Actions\Procurement\OrgSupplier\Hydrators\OrgSupplierHydratePurchaseOrders;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydratePurchaseOrders;
use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydratePurchaseOrders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeletePurchaseOrder
{
    use WithAttributes;
    use AsAction;


    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(PurchaseOrder $purchaseOrder): bool
    {
        if((in_array($purchaseOrder->state, [PurchaseOrderStateEnum::CREATING, PurchaseOrderStateEnum::SUBMITTED]))) {
            $orgParent = $purchaseOrder->orgParent;

            $purchaseOrder->items()->delete();
            $purchaseOrderDeleted = $purchaseOrder->delete();

            if (class_basename($orgParent) == 'OrgSupplier') {
                OrgSupplierHydratePurchaseOrders::dispatch($orgParent);
                SupplierHydratePurchaseOrders::dispatch($orgParent->supplier);
            } else {
                OrgAgentHydratePurchaseOrders::dispatch($orgParent);
                AgentHydratePurchaseOrders::dispatch($orgParent->agent);
            }

            OrganisationHydrateProcurement::dispatch($purchaseOrder->organisation);

            return $purchaseOrderDeleted;
        }

        throw ValidationException::withMessages(['purchase_order' => 'You can not delete this purchase order']);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function action(PurchaseOrder $purchaseOrder): bool
    {
        return $this->handle($purchaseOrder);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function asController(PurchaseOrder $purchaseOrder): bool
    {
        return $this->handle($purchaseOrder);
    }

    public function jsonResponse(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource($purchaseOrder);
    }
}
