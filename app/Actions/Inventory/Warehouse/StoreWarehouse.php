<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:25:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Warehouse;

use App\Actions\InertiaOrganisationAction;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateUniversalSearch;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWarehouse;
use App\Models\SysAdmin\Organisation;
use App\Models\Inventory\Warehouse;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreWarehouse extends InertiaOrganisationAction
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;


    public function handle(Organisation $organisation, $modelData): Warehouse
    {
        data_set($modelData, 'group_id', $organisation->group_id);
        /** @var Warehouse $warehouse */
        $warehouse = $organisation->warehouses()->create($modelData);
        $warehouse->stats()->create();
        OrganisationHydrateWarehouse::run($organisation);
        WarehouseHydrateUniversalSearch::dispatch($warehouse);

        return $warehouse;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'unique:warehouses', 'between:2,4', 'alpha_dash',
                       new IUnique(
                           table: 'warehouses',
                           extraConditions: [
                               ['column' => 'group_id', 'value' => $this->organisation->group_id],
                           ]
                       ),
                ],
            'name'     => ['required', 'max:250', 'string'],
            'source_id'=> ['sometimes','string','nullable'],
        ];
    }

    public function action(Organisation $organisation, array $modelData): Warehouse
    {
        $this->asAction = true;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $this->validatedData);
    }


    public function asController(Organisation $organisation, ActionRequest $request): Warehouse
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, $this->validatedData);
    }


    public function htmlResponse(Warehouse $warehouse): RedirectResponse
    {
        return Redirect::route('grp.inventory.warehouses.index');
    }


}
