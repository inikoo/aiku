<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 20:48:26 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier;

use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\GrpAction;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Procurement\OrgSupplier\StoreOrgSupplierFromSupplierInAgent;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateUniversalSearch;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydrateSuppliers;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateSuppliers;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Group;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreSupplier extends GrpAction
{
    use AsAction;
    use WithAttributes;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("procurement.".$this->group->id.".edit");
    }

    public function handle(Group|Agent $parent, array $modelData): Supplier
    {
        $addressData = Arr::get($modelData, 'address');
        Arr::forget($modelData, 'address');

        if (class_basename($parent) == 'Agent') {
            data_set($modelData, 'group_id', $parent->group_id);
            $group = $parent->group;
        } else {
            $group = $parent;
        }

        /** @var Supplier $supplier */
        $supplier = $parent->suppliers()->create($modelData);
        $supplier->stats()->create();
        SetCurrencyHistoricFields::run($supplier->currency, $supplier->created_at);

        StoreAddressAttachToModel::run($supplier, $addressData, ['scope' => 'contact']);
        $supplier->location = $supplier->getLocation();
        $supplier->save();
        $supplier->refresh();

        GroupHydrateSuppliers::run($group);


        if ($supplier->agent_id) {
            AgentHydrateSuppliers::dispatch($supplier->agent);
            StoreOrgSupplierFromSupplierInAgent::run($supplier);
        }

        SupplierHydrateUniversalSearch::dispatch($supplier);

        return $supplier;
    }

    public function rules(): array
    {
        return [
            'code'         => [
                'required',
                'max:9',
                'alpha_dash',
                new IUnique(
                    table: 'suppliers',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                    ]
                ),
            ],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email'        => ['nullable', 'email'],
            'phone'        => ['nullable', 'phone:AUTO'],
            'address'      => ['required', new ValidAddress()],
            'currency_id'  => ['required', 'exists:currencies,id'],
            'source_id'    => ['sometimes', 'nullable', 'string'],
            'source_slug'  => ['sometimes', 'nullable', 'string'],
            'deleted_at'   => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->get('contact_name') and !$this->get('company_name')) {
            $validator->errors()->add('contact_name', 'contact name or company name is required');
        }
    }

    public function action(Group|Agent $parent, $modelData): Supplier
    {
        $this->asAction = true;

        if (class_basename($parent) == 'Agent') {
            $group = $parent->group;
        } else {
            $group = $parent;
        }

        $this->initialisation($group, $modelData);

        return $this->handle(
            parent: $parent,
            modelData: $this->validatedData
        );
    }

    public function asController(ActionRequest $request): Supplier
    {
        $this->initialisation(app('group'), $request);

        return $this->handle(
            parent: group(),
            modelData: $this->validatedData
        );
    }

    public function inAgent(Agent $agent, ActionRequest $request): Supplier
    {
        $this->initialisation(app('group'), $request);

        return $this->handle(
            parent: $agent,
            modelData: $this->validatedData
        );
    }

    public function htmlResponse(Supplier $supplier): RedirectResponse
    {
        if ($supplier->owner_type == 'Agent') {
            /** @var \App\Models\SupplyChain\Agent $agent */
            $agent = $supplier->owner;

            return Redirect::route('grp.procurement.agents.show.suppliers.index', $agent->slug);
        }

        return Redirect::route('grp.procurement.suppliers.show', $supplier->slug);
    }
}
