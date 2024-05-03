<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:26:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\Hydrators\OrgAgentHydratePurchaseOrders;
use App\Actions\Procurement\OrgSupplier\Hydrators\OrgSupplierHydratePurchaseOrders;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydratePurchaseOrders;
use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydratePurchaseOrders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\PurchaseOrder;
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

    private OrgSupplier|OrgAgent $orgParent;

    public function handle(Organisation $organisation, OrgSupplier|OrgAgent $orgParent, array $modelData): PurchaseOrder
    {
        data_set($modelData, 'organisation_id', $organisation->id);
        data_set($modelData, 'group_id', $organisation->group_id);

        if (class_basename($orgParent) == 'OrgSupplier') {
            data_set($modelData, 'parent_type', 'Supplier');
            data_set($modelData, 'parent_id', $orgParent->supplier_id);
        } else {
            data_set($modelData, 'parent_type', 'Agent');
            data_set($modelData, 'parent_id', $orgParent->agent_id);
        }


        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $orgParent->purchaseOrders()->create($modelData);

        if (class_basename($orgParent) == 'OrgSupplier') {


            OrgSupplierHydratePurchaseOrders::dispatch($orgParent);
            SupplierHydratePurchaseOrders::dispatch($orgParent->supplier);
        } else {
            OrgAgentHydratePurchaseOrders::dispatch($orgParent);
            AgentHydratePurchaseOrders::dispatch($orgParent->agent);
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
                $this->strict ? new IUnique(
                    table: 'purchase_orders',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                    ]
                ) : null,
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
        $numberPurchaseOrdersStateCreating = $this->orgParent->purchaseOrders()->where('state', PurchaseOrderStateEnum::CREATING)->count();

        if ($this->strict && $numberPurchaseOrdersStateCreating >= 1) {
            $validator->errors()->add('purchase_order', 'Are you sure want to create new purchase order?');
        }

        //todo #170
        /*
        if ($this->strict && $this->parent->products->where('state', '<>', SupplierProductStateEnum::DISCONTINUED)->count() == 0) {
            $message = match (class_basename($this->parent)) {
                'OrgAgent' => 'You can not create purchase order if the agent dont have any product',
                'Supplier' => 'You can not create purchase order if the supplier dont have any product',
            };
            $validator->errors()->add('purchase_order', $message);
        }
        */
    }

    public function action(Organisation $organisation, OrgAgent|OrgSupplier $orgParent, array $modelData, bool $strict = true): \Illuminate\Http\RedirectResponse|PurchaseOrder
    {
        $this->asAction    = true;
        $this->orgParent   = $orgParent;
        $this->strict      = $strict;
        $this->initialisation($organisation, $modelData);


        return $this->handle($organisation, $orgParent, $this->validatedData);
    }

    public function inOrgAgent(Organisation $organisation, OrgAgent $orgAgent, ActionRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->orgParent = $orgAgent;


        $this->initialisation($organisation, $request);

        $purchaseOrder = $this->handle($organisation, $orgAgent, $this->validatedData);

        return redirect()->route('grp.org.procurement.purchase-orders.show', $purchaseOrder->slug);
    }

    public function inOrgSupplier(Organisation $organisation, OrgSupplier $orgSupplier, ActionRequest $request): \Illuminate\Http\RedirectResponse|PurchaseOrder
    {
        $this->orgParent = $orgSupplier;
        $this->initialisation($organisation, $request);

        $purchaseOrder = $this->handle($organisation, $orgSupplier, $this->validatedData);

        return redirect()->route('grp.org.procurement.purchase-orders.show', $purchaseOrder->slug);
    }
}
