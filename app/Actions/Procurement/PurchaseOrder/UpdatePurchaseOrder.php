<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 10:48:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Procurement\PurchaseOrderResource;
use App\Models\Procurement\PurchaseOrder;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdatePurchaseOrder extends OrgAction
{
    use WithActionUpdate;


    private PurchaseOrder $purchaseOrder;

    public function handle(PurchaseOrder $purchaseOrder, array $modelData): PurchaseOrder
    {
        $purchaseOrder = $this->update($purchaseOrder, $modelData, ['data']);
        HydratePurchaseOrder::dispatch($purchaseOrder);

        return $purchaseOrder;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("procurement.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'number'       => [
                'sometimes',
                'required',
                $this->strict ? 'alpha_dash' : 'string',
                $this->strict ? new IUnique(
                    table: 'purchase_orders',
                    extraConditions: [
                        [
                            'column' => 'organisation_id',
                            'value'  => $this->organisation->id,
                        ],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->purchaseOrder->id
                        ]
                    ]
                ) : null,
            ],
            'date'            => ['sometimes', 'date'],
            'parent_code'     => ['sometimes', 'required', 'string', 'max:256'],
            'parent_name'     => ['sometimes', 'required', 'string', 'max:256'],
            'last_fetched_at' => ['sometimes', 'date'],
        ];
    }

    public function action(PurchaseOrder $purchaseOrder, array $modelData, bool $strict = true, bool $audit=true): PurchaseOrder
    {
        if(!$audit) {
            PurchaseOrder::disableAuditing();
        }
        $this->asAction      = true;
        $this->strict        = $strict;
        $this->purchaseOrder = $purchaseOrder;
        $this->initialisation($purchaseOrder->organisation, $modelData);

        return $this->handle($purchaseOrder, $this->validatedData);
    }

    public function asController(PurchaseOrder $purchaseOrder, ActionRequest $request): PurchaseOrder
    {
        $this->purchaseOrder = $purchaseOrder;

        $this->initialisation($purchaseOrder->organisation, $request);

        return $this->handle($purchaseOrder, $this->validatedData);
    }

    public function jsonResponse(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource($purchaseOrder);
    }
}
