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
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePurchaseOrders;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgPartner;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StorePurchaseOrder extends OrgAction
{
    private OrgSupplier|OrgAgent|OrgPartner $parent;

    public function handle(Organisation $organisation, OrgSupplier|OrgAgent|OrgPartner $parent, array $modelData): PurchaseOrder
    {
        data_set($modelData, 'organisation_id', $organisation->id);
        data_set($modelData, 'group_id', $organisation->group_id);

        if (class_basename($parent) == 'OrgSupplier') {
            data_set($modelData, 'supplier_id', $parent->supplier_id);
        } elseif (class_basename($parent) == 'OrgAgent') {
            data_set($modelData, 'agent_id', $parent->agent_id);
        } elseif (class_basename($parent) == 'OrgPartner') {
            data_set($modelData, 'partner_id', $parent->organisation_id);
        }


        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $parent->purchaseOrders()->create($modelData);

        if (class_basename($parent) == 'OrgSupplier') {
            OrgSupplierHydratePurchaseOrders::dispatch($parent);
            SupplierHydratePurchaseOrders::dispatch($parent->supplier);
        } elseif (class_basename($parent) == 'OrgAgent') {
            OrgAgentHydratePurchaseOrders::dispatch($parent);
            AgentHydratePurchaseOrders::dispatch($parent->agent);
        }

        OrganisationHydratePurchaseOrders::dispatch($organisation);

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
        $numberPurchaseOrdersStateCreating = $this->parent->purchaseOrders()->where('state', PurchaseOrderStateEnum::CREATING)->count();

        if ($this->strict && $numberPurchaseOrdersStateCreating >= 1) {
            $validator->errors()->add('purchase_order', 'Are you sure want to create new purchase order?');
        }

        if ($this->strict && $this->parent->products()->where('status', true)->count() == 0) {
            $message = match (class_basename($this->parent)) {
                'OrgAgent'    => __("Agent don't have any product"),
                'OrgSupplier' => __("Supplier don't have any product"),
                'OrgPartner'  => __("Partner don't have any product"),
            };
            $validator->errors()->add('purchase_order', $message);
        }
    }

    public function action(Organisation $organisation, OrgAgent|OrgSupplier|OrgPartner $parent, array $modelData, bool $strict = true): \Illuminate\Http\RedirectResponse|PurchaseOrder
    {
        $this->asAction = true;
        $this->parent   = $parent;
        $this->strict   = $strict;
        $this->initialisation($organisation, $modelData);


        return $this->handle($organisation, $parent, $this->validatedData);
    }

    public function inOrgAgent(Organisation $organisation, OrgAgent $orgAgent, ActionRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->parent = $orgAgent;


        $this->initialisation($organisation, $request);

        $purchaseOrder = $this->handle($organisation, $orgAgent, $this->validatedData);

        return redirect()->route('grp.org.procurement.purchase-orders.show', $purchaseOrder->slug);
    }

    public function inOrgSupplier(Organisation $organisation, OrgSupplier $orgSupplier, ActionRequest $request): \Illuminate\Http\RedirectResponse|PurchaseOrder
    {
        $this->parent = $orgSupplier;
        $this->initialisation($organisation, $request);

        $purchaseOrder = $this->handle($organisation, $orgSupplier, $this->validatedData);

        return redirect()->route('grp.org.procurement.purchase-orders.show', $purchaseOrder->slug);
    }
}
