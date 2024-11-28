<?php
/*
 * author Arya Permana - Kirin
 * created on 28-11-2024-10h-56m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\SupplyChain\AgentSupplierPurchaseOrder;

use App\Actions\GrpAction;
use App\Actions\Procurement\WithNoStrictProcurementOrderRules;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderDeliveryStatusEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\AgentSupplierPurchaseOrder;
use App\Models\SupplyChain\Supplier;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreAgentSupplierPurchaseOrder extends GrpAction
{
    use WithNoStrictRules;
    use WithNoStrictProcurementOrderRules;

    public function handle(PurchaseOrder $purchaseOrder, Supplier $supplier, array $modelData): AgentSupplierPurchaseOrder
    {

        if (!Arr::get($modelData, 'reference')) {
            data_set(
                $modelData,
                'reference',
                $supplier->code.'-'.$purchaseOrder->reference
            );
        }
        if (!Arr::get($modelData, 'date')) {
            data_set($modelData, 'date', now());
        }
        if (!Arr::get($modelData, 'currency_id')) {
            data_set($modelData, 'currency_id', $supplier->currency_id);
        }
        // dd($parent);
        /** @var AgentSupplierPurchaseOrder $agentSupplierPurchaseOrder */
        $agentSupplierPurchaseOrder = $supplier->agentSupplierPurchaseOrder()->create($modelData);

        return $agentSupplierPurchaseOrder;
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
        $rules = [
            'reference'       => [
                'sometimes',
                'required',
                $this->strict ? 'alpha_dash' : 'string',
                $this->strict ? new IUnique(
                    table: 'agent_supplier_purchase_orders',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                    ]
                ) : null,
            ],
            'state'           => ['sometimes', 'required', Rule::enum(PurchaseOrderStateEnum::class)],
            'delivery_status' => ['sometimes', 'required', Rule::enum(PurchaseOrderDeliveryStatusEnum::class)],
            'cost_items'      => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost_shipping'   => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost_total'      => ['sometimes', 'required', 'numeric', 'min:0'],
            'date'            => ['sometimes', 'required'],
            'currency_id'     => ['sometimes', 'required'],
        ];

        if (!$this->strict) {
            $rules = $this->noStrictStoreRules($rules);
            $rules = $this->noStrictProcurementOrderRules($rules);
            $rules = $this->noStrictPurchaseOrderDatesRules($rules);
        }

        return $rules;
    }

    public function action(PurchaseOrder $purchaseOrder, Supplier $supplier, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): AgentSupplierPurchaseOrder
    {
        if (!$audit) {
            AgentSupplierPurchaseOrder::disableAuditing();
        }
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($supplier->group, $modelData);


        return $this->handle($purchaseOrder, $supplier, $this->validatedData);
    }
}
