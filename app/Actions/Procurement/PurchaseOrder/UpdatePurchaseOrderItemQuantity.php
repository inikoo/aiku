<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 05 May 2023 16:55:25 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use Lorisleiva\Actions\ActionRequest;

class UpdatePurchaseOrderItemQuantity
{
    use WithActionUpdate;

    public function handle(PurchaseOrderItem $purchaseOrderItem, array $modelData): PurchaseOrderItem
    {
        $updatedItem = $this->update($purchaseOrderItem, $modelData);

        if($updatedItem->unit_quantity == 0) {
            RemoveItemPurchaseOrder::run($updatedItem);
        }

        return $purchaseOrderItem;
    }

    public function action(PurchaseOrderItem $purchaseOrderItem, array $modelData): PurchaseOrderItem
    {
        return $this->handle($purchaseOrderItem, $modelData);
    }

    public function asController(PurchaseOrderItem $purchaseOrderItem, ActionRequest $request): PurchaseOrderItem
    {
        $request->validate();

        return $this->handle($purchaseOrderItem, $request->all());
    }

    public function jsonResponse(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource($purchaseOrder);
    }
}
