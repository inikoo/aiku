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
use App\Actions\Procurement\WithPrepareDeliveryStoreFields;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydratePurchaseOrders;
use App\Actions\SupplyChain\Supplier\Hydrators\SupplierHydratePurchaseOrders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePurchaseOrders;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStatusEnum;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgPartner;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\PurchaseOrder;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StorePurchaseOrder extends OrgAction
{
    use WithPrepareDeliveryStoreFields;
    use WithNoStrictRules;

    private OrgSupplier|OrgAgent|OrgPartner $parent;

    public function handle(OrgSupplier|OrgAgent|OrgPartner $parent, array $modelData): PurchaseOrder
    {

        $modelData = $this->prepareDeliveryStoreFields($parent, $modelData);

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $parent->purchaseOrders()->create($modelData);

        if (class_basename($parent) == 'OrgSupplier') {
            OrgSupplierHydratePurchaseOrders::dispatch($parent)->delay($this->hydratorsDelay);
            SupplierHydratePurchaseOrders::dispatch($parent->supplier)->delay($this->hydratorsDelay);
        } elseif (class_basename($parent) == 'OrgAgent') {
            OrgAgentHydratePurchaseOrders::dispatch($parent)->delay($this->hydratorsDelay);
            AgentHydratePurchaseOrders::dispatch($parent->agent)->delay($this->hydratorsDelay);
        }

        OrganisationHydratePurchaseOrders::dispatch($purchaseOrder->organisation)->delay($this->hydratorsDelay);

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
        $rules = [
            'reference'       => [
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
            'state'           => ['sometimes', 'required', Rule::enum(PurchaseOrderStateEnum::class)],
            'status'          => ['sometimes', 'required', Rule::enum(PurchaseOrderStatusEnum::class)],
            'cost_items'      => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost_shipping'   => ['sometimes', 'required', 'numeric', 'min:0'],
            'cost_total'      => ['sometimes', 'required', 'numeric', 'min:0'],
            'currency_id'     => ['sometimes', 'required', 'exists:currencies,id'],
            'org_exchange'    => ['sometimes', 'required', 'numeric', 'min:0'],
            'grp_exchange'    => ['sometimes', 'required', 'numeric', 'min:0'],
            'parent_code'     => ['sometimes', 'required', 'string', 'max:256'],
            'parent_name'     => ['sometimes', 'required', 'string', 'max:256'],

        ];

        if (!$this->strict) {


            $rules = $this->noStrictStoreRules($rules);
            $rules['submitted_at'] = ['sometimes', 'nullable', 'date'];
            $rules['confirmed_at'] = ['sometimes', 'nullable', 'date'];
            $rules['manufactured_at'] = ['sometimes', 'nullable', 'date'];
            $rules['received_at'] = ['sometimes', 'nullable', 'date'];
            $rules['checked_at'] = ['sometimes', 'nullable', 'date'];
            $rules['settled_at'] = ['sometimes', 'nullable', 'date'];

        }

        return $rules;

    }

    public function afterValidator(Validator $validator): void
    {
        $numberPurchaseOrdersStateCreating = $this->parent->purchaseOrders()->where('state', PurchaseOrderStateEnum::IN_PROCESS)->count();

        if ($this->strict && $numberPurchaseOrdersStateCreating >= 1) {
            $validator->errors()->add('purchase_order', 'Are you sure want to create new purchase order?');
        }

        if ($this->strict && $this->parent->orgSupplierProducts()->where('is_available', true)->count() == 0) {
            $message = match (class_basename($this->parent)) {
                'OrgAgent'    => __("Agent don't have any product"),
                'OrgSupplier' => __("Supplier don't have any product"),
                'OrgPartner'  => __("Partner don't have any product"),
            };
            $validator->errors()->add('purchase_order', $message);
        }
    }

    public function action(OrgAgent|OrgSupplier|OrgPartner $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): PurchaseOrder
    {
        if (!$audit) {
            PurchaseOrder::disableAuditing();
        }
        $this->asAction = true;
        $this->parent   = $parent;
        $this->strict   = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($parent->organisation, $modelData);


        return $this->handle($parent, $this->validatedData);
    }

    public function inOrgAgent(OrgAgent $orgAgent, ActionRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->parent = $orgAgent;


        $this->initialisation($orgAgent->organisation, $request);

        $purchaseOrder = $this->handle($orgAgent, $this->validatedData);

        return redirect()->route('grp.org.procurement.purchase_orders.show', $purchaseOrder->slug);
    }

    public function inOrgSupplier(OrgSupplier $orgSupplier, ActionRequest $request): \Illuminate\Http\RedirectResponse|PurchaseOrder
    {
        $this->parent = $orgSupplier;
        $this->initialisation($orgSupplier->organisation, $request);

        $purchaseOrder = $this->handle($orgSupplier, $this->validatedData);

        return redirect()->route('grp.org.procurement.purchase_orders.show', $purchaseOrder->slug);
    }
}
