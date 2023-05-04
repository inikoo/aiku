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
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
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
        if(($purchaseOrder->items()->count() > 0) && ($purchaseOrder->status == PurchaseOrderStatusEnum::PROCESSING)) {
            $purchaseOrder->delete();

            $parent = $purchaseOrder->provider;

            if (class_basename($parent) == 'Supplier') {
                SupplierHydratePurchaseOrder::dispatch($parent);
            } else {
                AgentHydratePurchaseOrder::dispatch($parent);
            }

            TenantHydrateProcurement::dispatch(app('currentTenant'));
        }

        throw ValidationException::withMessages(['purchase_order' => 'You can not delete this purchase order']);
    }

    public function action(PurchaseOrder $purchaseOrder): bool
    {
        return $this->handle($purchaseOrder);
    }

    public function asController(PurchaseOrder $purchaseOrder): bool
    {
        return $this->handle($purchaseOrder);
    }

    public function jsonResponse(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource($purchaseOrder);
    }
}
