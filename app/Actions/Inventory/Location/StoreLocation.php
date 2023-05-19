<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Location;

use App\Actions\Inventory\Location\Hydrators\LocationHydrateUniversalSearch;
use App\Actions\Inventory\Warehouse\HydrateWarehouse;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Rules\CaseSensitive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreLocation
{
    use AsAction;
    use WithAttributes;

    private bool $asAction=false;

    public function handle(WarehouseArea|Warehouse $parent, array $modelData): Location
    {
        if (class_basename($parent::class) == 'WarehouseArea') {
            $modelData['warehouse_id'] = $parent->warehouse_id;
        } else {
            HydrateWarehouse::run($parent);
        }

        /** @var Location $location */
        $location = $parent->locations()->create($modelData);
        $location->stats()->create();

        LocationHydrateUniversalSearch::dispatch($location);

        return $location;
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
            'code'         => ['required', 'unique:tenant.locations', 'between:2,64', 'alpha_dash', new CaseSensitive('locations')],
        ];
    }

    public function asController(Warehouse $warehouse, ActionRequest $request): Location
    {
        $request->validate();

        return $this->handle($warehouse, $request->validated());
    }


    public function inWarehouseArea(WarehouseArea $warehouseArea, ActionRequest $request): Location
    {
        $request->validate();

        return $this->handle($warehouseArea, $request->validated());
    }

    public function htmlResponse(Location $location): RedirectResponse
    {
        if(!$location->warehouse_area_id) {
            return Redirect::route('inventory.warehouses.show.locations.show', [
                $location->warehouse->slug,
                $location->slug
            ]);
        } else {
            return Redirect::route('inventory.warehouses.show.warehouse-areas.show.locations.show', [
                $location->warehouse->slug,
                $location->warehouseArea->slug,
                $location->slug
            ]);
        }
    }

    public function action(WarehouseArea|Warehouse $parent, array $objectData): Location
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }
}
