<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 29 Nov 2023 23:07:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier;

use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\GrpAction;
use App\Actions\Procurement\Agent\Hydrators\AgentHydrateSuppliers;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateUniversalSearch;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProcurement;
use App\Models\SysAdmin\Group;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
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

    private bool $asAction = false;

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
            $group=$parent->group;
        } else {
            $group=$parent;
        }

        /** @var Supplier $supplier */
        $supplier = $parent->suppliers()->create($modelData);
        $supplier->stats()->create();
        SetCurrencyHistoricFields::run($supplier->currency, $supplier->created_at);

        StoreAddressAttachToModel::run($supplier, $addressData, ['scope' => 'contact']);
        $supplier->location = $supplier->getLocation();
        $supplier->save();

        GroupHydrateProcurement::run($group);

        if ($supplier->agent_id) {
            AgentHydrateSuppliers::dispatch($supplier->agent);
        }

        SupplierHydrateUniversalSearch::dispatch($supplier);

        return $supplier;
    }

    public function rules(): array
    {
        return [
            'code'         => ['required', 'max:9', 'alpha_dash',
                               new IUnique(
                                   table: 'suppliers',
                                   extraConditions: [
                                       ['column' => 'group_id', 'value' => $this->group->id],
                                   ]
                               ),
            ],
            'contact_name'   => ['nullable', 'string', 'max:255'],
            'company_name'   => ['nullable', 'string', 'max:255'],
            'email'          => ['nullable', 'email'],
            'phone'          => ['nullable', 'phone:AUTO'],
            'address'        => ['required', new ValidAddress()],
            'currency_id'    => ['required', 'exists:currencies,id'],
            'source_id'      => ['sometimes', 'nullable', 'string'],
            'source_slug'    => ['sometimes', 'nullable', 'string'],
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

        if(class_basename($parent)=='Agent') {
            $group=$parent->group;
        } else {
            $group=$parent;
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
            /** @var Agent $agent */
            $agent = $supplier->owner;

            return Redirect::route('grp.procurement.agents.show.suppliers.index', $agent->slug);
        }

        return Redirect::route('grp.procurement.suppliers.show', $supplier->slug);
    }
}
