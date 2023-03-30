<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 11:34:34 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\LocationTabsEnum;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Location $location
 */
class ShowLocation extends InertiaAction
{
    use HasUILocation;
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.locations.edit');
        return $request->user()->hasPermissionTo("inventory.view");
    }


    public function inTenant(Location $location, ActionRequest $request): void
    {
        $this->location = $location;
        //$this->validateAttributes();
        $this->initialisation($request);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse, Location $location, ActionRequest $request): void
    {
        $this->location = $location;
        //$this->validateAttributes();
        $this->initialisation($request);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseArea(WarehouseArea $warehouseArea, Location $location, ActionRequest $request): void
    {
        $this->location = $location;
        $this->initialisation($request);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function InWarehouseInWarehouseArea(Warehouse $warehouse, WarehouseArea $warehouseArea, Location $location, ActionRequest $request): void
    {
        $this->location = $location;
        $this->initialisation($request);
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Inventory/Location',
            [
                'title'       => __('location'),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->location),
                'pageHead'    => [
                    'icon'  => 'fal fa-inventory',
                    'title' => $this->location->code,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,

                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => LocationTabsEnum::navigation()

                ],
            ]
        );
    }


    public function jsonResponse(): JsonResource
    {
        return new JsonResource($this->location);
    }
}
