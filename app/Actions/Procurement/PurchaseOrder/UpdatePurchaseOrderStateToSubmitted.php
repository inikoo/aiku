<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\OrgAction;
use App\Actions\Procurement\PurchaseOrder\Traits\HasPurchaseOrderHydrators;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePurchaseOrderStateToSubmitted extends OrgAction
{
    use WithActionUpdate;
    use AsAction;
    use HasPurchaseOrderHydrators;


    /**
     * @var \App\Models\Procurement\PurchaseOrder
     */
    private PurchaseOrder $purchaseOrder;

    public function handle(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $data = [
            'state' => PurchaseOrderStateEnum::SUBMITTED
        ];

        $purchaseOrder->items()->update($data);

        if ($purchaseOrder->state !== PurchaseOrderStateEnum::CREATING) {
            $data[$purchaseOrder->state->value.'_at'] = null;
        }

        $data['submitted_at'] = now();

        $purchaseOrder = $this->update($purchaseOrder, $data);

        $this->purchaseOrderHydrate($purchaseOrder);

        return $purchaseOrder;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");
    }

    public function afterValidator(Validator $validator): void
    {
        if (!in_array($this->purchaseOrder->state, [PurchaseOrderStateEnum::CREATING, PurchaseOrderStateEnum::CONFIRMED])) {
            $validator->errors()->add('status', __('Purchase order can only be submitted if it is in creating or confirmed state'));
        }
    }


    public function action(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $this->asAction      = true;
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, []);

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
