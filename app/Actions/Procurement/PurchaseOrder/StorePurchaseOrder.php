<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:26:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\PurchaseOrder;

use App\Actions\Procurement\Supplier\Hydrators\SupplierHydratePurchaseOrders;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydratePurchaseOrders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use App\Models\Procurement\PurchaseOrder;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StorePurchaseOrder
{
    use AsAction;
    use WithAttributes;

    private bool $force;

    private Supplier|Agent $provider;

    public function handle(Organisation $organisation, Agent|Supplier $provider, array $modelData): PurchaseOrder
    {

        data_set($modelData, 'organisation_id', $organisation->id);
        data_set($modelData, 'group_id', $organisation->group_id);

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $provider->purchaseOrders()->create($modelData);

        if (class_basename($provider) == 'Supplier') {
            SupplierHydratePurchaseOrders::dispatch($provider);
        } else {
            AgentHydratePurchaseOrders::dispatch($provider);
        }

        OrganisationHydrateProcurement::dispatch($organisation);

        return $purchaseOrder;
    }

    public function rules(): array
    {
        return [
            'number'      => ['sometimes', 'required', 'numeric', 'unique:purchase_orders'],
            'date'        => ['sometimes', 'required', 'date'],
            'currency_id' => ['sometimes', 'required', 'exists:currencies,id'],
            'exchange'    => ['sometimes', 'required', 'numeric']
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $numberPurchaseOrdersStateCreating = $this->provider->purchaseOrders()->where('state', PurchaseOrderStateEnum::CREATING)->count();

        if (!$this->force && $numberPurchaseOrdersStateCreating >= 1) {
            $validator->errors()->add('purchase_order', 'Are you sure want to create new purchase order?');
        }

        if ($this->provider->products->where('state', '<>', SupplierProductStateEnum::DISCONTINUED)->count() == 0) {
            $message = match (class_basename($this->provider)) {
                'OrgAgent'             => 'You can not create purchase order if the agent dont have any product',
                'Supplier'             => 'You can not create purchase order if the supplier dont have any product',
            };
            $validator->errors()->add('purchase_order', $message);
        }
    }

    public function action(Organisation $organisation, Agent|Supplier $provider, array $modelData, bool $force = false): \Illuminate\Http\RedirectResponse|PurchaseOrder
    {
        $this->provider = $provider;
        $this->force    = $force;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($organisation, $provider, $validatedData);
    }

    public function inAgent(Organisation $organisation, Agent $agent, ActionRequest $request): \Illuminate\Http\RedirectResponse
    {
        $modelData = [
            'number'      => rand(1111, 9999),
            'date'        => now(),
            'currency_id' => $agent->currency_id,
        ];

        $this->force    = $request->force ?? false;
        $this->provider = $agent;
        $request->validate();

        $purchaseOrder = $this->handle($organisation, $agent, $modelData);

        return redirect()->route('grp.procurement.purchase-orders.show', $purchaseOrder->slug);
    }

    public function inSupplier(Organisation $organisation, Supplier $supplier, ActionRequest $request): \Illuminate\Http\RedirectResponse|PurchaseOrder
    {
        $modelData = [
            'number'      => rand(1111, 9999),
            'date'        => now(),
            'currency_id' => $supplier->currency_id
        ];

        $this->force    = false;
        $this->provider = $supplier;
        $request->validate();

        $purchaseOrder = $this->handle($organisation, $supplier, $modelData);

        return redirect()->route('grp.procurement.purchase-orders.show', $purchaseOrder->slug);
    }
}
