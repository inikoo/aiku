<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Location;

use App\Actions\OrgAction;
use App\Actions\Inventory\Location\Hydrators\LocationHydrateUniversalSearch;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateLocations;
use App\Actions\Inventory\WarehouseArea\Hydrators\WarehouseAreaHydrateLocations;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateLocations;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateLocations;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Rules\IUnique;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreLocation extends OrgAction
{
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
        GroupHydrateLocations::run($organisation->group);
        OrganisationHydrateLocations::dispatch($organisation);

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

        return $request->user()->hasPermissionTo("locations.{$this->warehouse->id}.edit");
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
            'max_weight'   => ['sometimes', 'nullable', 'numeric', 'min:0.1', 'max:1000000'],
            'max_volume'   => ['sometimes', 'nullable', 'numeric', 'min:0.1', 'max:1000000'],
            'source_id'    => ['sometimes', 'string'],
            'deleted_at'   => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function inWarehouse(Warehouse $warehouse, ActionRequest $request): Location
    {
        $this->warehouse = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($warehouse, $this->validatedData);
    }


    public function inWarehouseArea(WarehouseArea $warehouseArea, ActionRequest $request): Location
    {
        $this->warehouse = $warehouseArea->warehouse;
        $this->initialisationFromWarehouse($warehouseArea->warehouse, $request);

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

        $this->initialisationFromWarehouse($this->warehouse, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    public function htmlResponse(Location $location): RedirectResponse
    {
        if (!$location->warehouse_area_id) {
            return Redirect::route('grp.org.warehouses.show.infrastructure.locations.show', [
                $location->organisation->slug,
                $location->warehouse->slug,
                $location->slug
            ]);
        } else {
            return Redirect::route('grp.org.warehouses.show.infrastructure.warehouse-areas.show.locations.show', [
                $location->organisation->slug,
                $location->warehouse->slug,
                $location->warehouseArea->slug,
                $location->slug
            ]);
        }
    }

    public string $commandSignature = 'locations:create {warehouse : warehouse slug} {code} {--a|area=} {--w|max_weight=} {--u|max_volume=} ';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        try {
            $warehouse = Warehouse::where('slug', $command->argument('warehouse'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }
        $this->warehouse = $warehouse;
        $parent          = $warehouse;
        $this->setRawAttributes([
            'code' => $command->argument('code'),
        ]);

        if($command->option('max_weight')) {
            $this->fill([
                'max_weight' => $command->option('max_weight'),
            ]);
        }
        if($command->option('max_volume')) {
            $this->fill([
                'max_volume' => $command->option('max_volume'),
            ]);
        }


        if($command->option('area')) {
            try {
                $warehouseArea = WarehouseArea::where('slug', $command->option('area'))->firstOrFail();
            } catch (Exception) {
                $command->error("Warehouse area {$command->option('area')} not found");
                return 1;
            }
            $this->warehouse = $warehouseArea->warehouse;

            $parent = $warehouseArea;
        }


        try {
            $validatedData = $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $location = $this->handle($parent, $validatedData);

        $command->info("Location: $location->code created successfully 🎉");

        return 0;
    }

}
