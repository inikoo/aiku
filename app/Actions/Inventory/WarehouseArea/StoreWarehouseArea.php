<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 04 Oct 2021 12:34:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateWarehouseAreas;
use App\Actions\Inventory\WarehouseArea\Search\WarehouseAreaRecordSearch;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateWarehouseAreas;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWarehouseAreas;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Rules\IUnique;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreWarehouseArea extends OrgAction
{
    public function handle(Warehouse $warehouse, array $modelData): WarehouseArea
    {
        data_set($modelData, 'group_id', $warehouse->group_id);
        data_set($modelData, 'organisation_id', $warehouse->organisation_id);
        /** @var WarehouseArea $warehouseArea */
        $warehouseArea = $warehouse->warehouseAreas()->create($modelData);
        $warehouseArea->stats()->create();
        WarehouseAreaRecordSearch::dispatch($warehouseArea);
        GroupHydrateWarehouseAreas::dispatch($warehouse->group);
        OrganisationHydrateWarehouseAreas::dispatch($warehouse->organisation);
        WarehouseHydrateWarehouseAreas::dispatch($warehouse);

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
            'code'      => [
                'required',
                'max:16',
                'alpha_dash',
                new IUnique(
                    table: 'warehouse_areas',
                    extraConditions: [
                        ['column' => 'warehouse_id', 'value' => $this->warehouse->id],
                    ]
                ),
            ],
            'name'        => ['required', 'max:250', 'string'],
            'source_id'   => ['sometimes', 'string'],
            'fetched_at'  => ['sometimes', 'date'],
        ];
    }


    public function asController(Warehouse $warehouse, ActionRequest $request): WarehouseArea
    {
        $this->warehouse = $warehouse;
        $this->initialisation($warehouse->organisation, $request);

        return $this->handle($warehouse, $this->validatedData);
    }

    public function action(Warehouse $warehouse, array $modelData): WarehouseArea
    {
        $this->asAction  = true;
        $this->warehouse = $warehouse;
        $this->initialisation($warehouse->organisation, $modelData);

        return $this->handle($warehouse, $this->validatedData);
    }

    public function htmlResponse(WarehouseArea $warehouseArea): RedirectResponse
    {
        return Redirect::route('grp.org.warehouses.show.infrastructure.warehouse-areas.index', $warehouseArea->warehouse->slug);
    }

    public string $commandSignature = 'warehouse-areas:create {warehouse : warehouse slug} {code} {name}';

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
        $this->setRawAttributes([
            'code' => $command->argument('code'),
            'name' => $command->argument('name'),
        ]);

        try {
            $validatedData = $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $warehouseArea = $this->handle($warehouse, $validatedData);

        $command->info("Warehouse area $warehouseArea->code created successfully 🎉");

        return 0;
    }

}
