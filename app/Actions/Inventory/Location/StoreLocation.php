<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Location;

use App\Actions\InertiaOrganisationAction;
use App\Actions\Inventory\Location\Hydrators\LocationHydrateUniversalSearch;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateLocations;
use App\Actions\Inventory\WarehouseArea\Hydrators\WarehouseAreaHydrateLocations;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWarehouse;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreLocation extends InertiaOrganisationAction
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;


    public function handle(WarehouseArea|Warehouse $parent, array $modelData): Location
    {
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);

        if (class_basename($parent::class) == 'WarehouseArea') {
            $modelData['warehouse_id'] = $parent->warehouse_id;
            $organisation              = $parent->warehouse->organisation;
        } else {
            $organisation = $parent->organisation;
        }

        /** @var Location $location */
        $location = $parent->locations()->create($modelData);
        $location->stats()->create();
        OrganisationHydrateWarehouse::dispatch($organisation);

        if ($location->warehouse_area_id) {
            WarehouseAreaHydrateLocations::dispatch($location->warehouseArea);
        }

        WarehouseHydrateLocations::dispatch($location->warehouse);
        LocationHydrateUniversalSearch::dispatch($location);

        return $location;
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
            'code'       => [
                'required',
                'max:64',
                'alpha_dash',
                new IUnique(
                    table: 'locations',
                    extraConditions: [
                        ['column' => 'warehouse_id', 'value' => $this->warehouse->id],
                    ]
                ),
            ],
            'max_weight' => ['nullable', 'numeric', 'min:0.1', 'max:1000000'],
            'max_volume' => ['nullable', 'numeric', 'min:0.1', 'max:1000000'],
            'source_id'  => ['sometimes', 'string'],
        ];
    }

    public function asController(Warehouse $warehouse, ActionRequest $request): Location
    {
        $this->warehouse = $warehouse;
        $this->initialisation($warehouse->organisation, $request);

        return $this->handle($warehouse, $this->validatedData);
    }


    public function inWarehouseArea(WarehouseArea $warehouseArea, ActionRequest $request): Location
    {
        $this->warehouse = $warehouseArea->warehouse;
        $this->initialisation($warehouseArea->organisation, $request);

        return $this->handle($warehouseArea, $this->validatedData);
    }

    public function action(WarehouseArea|Warehouse $parent, array $modelData): Location
    {
        $this->asAction = true;

        if(class_basename($parent::class) == 'WarehouseArea') {
            $this->warehouse = $parent->warehouse;
        } else {
            $this->warehouse = $parent;
        }

        $this->initialisation($parent->organisation, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    public function htmlResponse(Location $location): RedirectResponse
    {
        if (!$location->warehouse_area_id) {
            return Redirect::route('grp.org.inventory.warehouses.show.locations.show', [
                $location->warehouse->slug,
                $location->slug
            ]);
        } else {
            return Redirect::route('grp.org.inventory.warehouses.show.warehouse-areas.show.locations.show', [
                $location->warehouse->slug,
                $location->warehouseArea->slug,
                $location->slug
            ]);
        }
    }


}
