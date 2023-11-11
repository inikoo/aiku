<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:25:15 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Warehouse;

use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateUniversalSearch;
use App\Actions\Organisation\Organisation\Hydrators\OrganisationHydrateWarehouse;
use App\Models\Inventory\Warehouse;
use App\Rules\CaseSensitive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreWarehouse
{
    use AsAction;
    use WithAttributes;

    private bool $asAction=false;


    public function handle($modelData): Warehouse
    {
        $warehouse = Warehouse::create($modelData);
        $warehouse->stats()->create();
        OrganisationHydrateWarehouse::run(app('currentTenant'));
        WarehouseHydrateUniversalSearch::dispatch($warehouse);

        return $warehouse;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    }

    public function rules(): array
    {
        return [
            'code'         => ['required', 'unique:tenant.warehouses', 'between:2,4', 'alpha_dash', new CaseSensitive('warehouses')],
            'name'         => ['required', 'max:250', 'string'],
        ];
    }

    public function action(array $objectData): Warehouse
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }


    public function asController(ActionRequest $request): Warehouse
    {
        $request->validate();

        return $this->handle($request->validated());
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function htmlResponse(Warehouse $warehouse): RedirectResponse
    {
        return Redirect::route('inventory.warehouses.index');
    }




}
