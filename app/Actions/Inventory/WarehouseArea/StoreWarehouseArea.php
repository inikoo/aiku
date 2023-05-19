<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 04 Oct 2021 12:34:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Actions\Inventory\WarehouseArea\Hydrators\WarehouseAreaHydrateUniversalSearch;
use App\Models\Inventory\WarehouseArea;
use App\Models\Inventory\Warehouse;
use App\Rules\CaseSensitive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreWarehouseArea
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(Warehouse $warehouse, array $modelData): WarehouseArea
    {
        /** @var WarehouseArea $warehouseArea */
        $warehouseArea = $warehouse->warehouseAreas()->create($modelData);
        $warehouseArea->stats()->create();
        WarehouseAreaHydrateUniversalSearch::dispatch($warehouseArea);

        return $warehouseArea;
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
            'code' => ['required', 'unique:tenant.warehouses', 'between:2,4', 'alpha', new CaseSensitive('warehouses')],
            'name' => ['required', 'max:250', 'string'],
        ];
    }


    public function asController(Warehouse $warehouse, ActionRequest $request): WarehouseArea
    {
        $request->validate();

        return $this->handle($warehouse, $request->validated());
    }

    public function htmlResponse(WarehouseArea $warehouseArea): RedirectResponse
    {
        return Redirect::route('inventory.warehouses.show.warehouse-areas.index', $warehouseArea->warehouse->slug);
    }

    public function action(Warehouse $warehouse, array $objectData): WarehouseArea
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($warehouse, $validatedData);
    }
}
