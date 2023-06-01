<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 09 May 2023 17:02:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\Procurement\PurchaseOrder\Traits\HasHydrators;
use App\Actions\WithActionUpdate;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateStateToSettledPurchaseOrder
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
            'state' => PurchaseOrderStateEnum::SETTLED
        ];

        if ($purchaseOrder->state == PurchaseOrderStateEnum::CHECKED) {
            $purchaseOrder->items()->update($data);

            $data[$purchaseOrder->state->value . '_at'] = null;
            $data['settled_at']                         = now();

            $purchaseOrder = $this->update($purchaseOrder, $data);

            $this->purchaseOrderHydrate($purchaseOrder);

            return $purchaseOrder;
        }

        throw ValidationException::withMessages(['status' => 'You can not change the status to settled']);
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
