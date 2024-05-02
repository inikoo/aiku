<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:26:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\OrgAction;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydratePurchaseOrders;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydratePurchaseOrders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StorePurchaseOrder extends OrgAction
{
    use AsAction;
    use WithAttributes;

    private Supplier|Agent $parent;

    public function handle(Organisation $organisation, Agent|Supplier $parent, array $modelData): PurchaseOrder
    {
        data_set($modelData, 'organisation_id', $organisation->id);
        data_set($modelData, 'group_id', $organisation->group_id);

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $parent->purchaseOrders()->create($modelData);

        if (class_basename($parent) == 'Supplier') {
            SupplierHydratePurchaseOrders::dispatch($parent);
        } else {
            AgentHydratePurchaseOrders::dispatch($parent);
        }

        OrganisationHydrateProcurement::dispatch($organisation);

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
            'number'          => [
                'sometimes',
                'required',
                $this->strict ? 'alpha_dash' : 'string',
                new IUnique(
                    table: 'purchase_orders',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                    ]
                ),
            ],
            'date'            => ['required', 'date'],
            'submitted_at'    => ['sometimes', 'nullable', 'date'],
            'confirmed_at'    => ['sometimes', 'nullable', 'date'],
            'manufactured_at' => ['sometimes', 'nullable', 'date'],
            'received_at'     => ['sometimes', 'nullable', 'date'],
            'checked_at'      => ['sometimes', 'nullable', 'date'],
            'settled_at'      => ['sometimes', 'nullable', 'date'],
            'state'           => ['sometimes', 'required', Rule::enum(PurchaseOrderStateEnum::class)],
            'status'          => ['sometimes', 'required', Rule::enum(PurchaseOrderStatusEnum::class)],
            'cost_items'      => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost_shipping'   => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost_total'      => ['sometimes', 'required', 'numeric', 'min:0'],
            'created_at'      => ['sometimes', 'required', 'date'],
            'cancelled_at'    => ['sometimes', 'nullable', 'date'],
            'currency_id'     => ['sometimes', 'required', 'exists:currencies,id'],
            'org_exchange'    => ['sometimes', 'required', 'numeric', 'min:0'],
            'group_exchange'  => ['sometimes', 'required', 'numeric', 'min:0'],
            'source_id'       => ['sometimes', 'required', 'string', 'max:64'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $numberPurchaseOrdersStateCreating = $this->parent->purchaseOrders()->where('state', PurchaseOrderStateEnum::CREATING)->count();

        if ($this->strict && $numberPurchaseOrdersStateCreating >= 1) {
            $validator->errors()->add('purchase_order', 'Are you sure want to create new purchase order?');
        }

        if ($this->strict && $this->parent->products->where('state', '<>', SupplierProductStateEnum::DISCONTINUED)->count() == 0) {
            $message = match (class_basename($this->parent)) {
                'OrgAgent' => 'You can not create purchase order if the agent dont have any product',
                'Supplier' => 'You can not create purchase order if the supplier dont have any product',
            };
            $validator->errors()->add('purchase_order', $message);
        }
    }

    public function action(Organisation $organisation, Agent|Supplier $parent, array $modelData, bool $strict = true): \Illuminate\Http\RedirectResponse|PurchaseOrder
    {
        $this->asAction = true;
        $this->parent   = $parent;
        $this->strict   = $strict;
        $this->initialisation($organisation, $modelData);


        return $this->handle($organisation, $parent, $this->validatedData);
    }

    public function inAgent(Organisation $organisation, Agent $agent, ActionRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->parent = $agent;


        $this->initialisation($organisation, $request);

        $purchaseOrder = $this->handle($organisation, $agent, $this->validatedData);

        return redirect()->route('grp.org.procurement.purchase-orders.show', $purchaseOrder->slug);
    }

    public function inSupplier(Organisation $organisation, Supplier $supplier, ActionRequest $request): \Illuminate\Http\RedirectResponse|PurchaseOrder
    {
        $this->parent = $supplier;
        $this->initialisation($organisation, $request);

        $purchaseOrder = $this->handle($organisation, $supplier, $this->validatedData);

        return redirect()->route('grp.org.procurement.purchase-orders.show', $purchaseOrder->slug);
    }
}
