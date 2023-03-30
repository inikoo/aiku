<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Location\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Inventory\LocationResource;
use App\Models\Inventory\Location;
use App\Models\Inventory\WarehouseArea;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

class EditLocation extends InertiaAction
{
    use HasUILocation;
    public function handle(Location $location): Location
    {
        return $location;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.locations.edit');
        return $request->user()->hasPermissionTo("inventory.warehouses.view");
    }

    public function asController(Location $location, ActionRequest $request): Location
    {
        $this->initialisation($request);

        return $this->handle($location);
    }

    public function inTenant(Location $location, ActionRequest $request): Location
    {
        $this->initialisation($request);

        return $this->handle($location);
    }

    public function inWarehouseArea(WarehouseArea $warehouseArea, Location $location, ActionRequest $request): Location
    {
        $this->initialisation($request);

        return $this->handle($location);
    }

    public function htmlResponse(Location $location): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('location'),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $location),
                'pageHead'    => [
                    'title'     => $location->code,
                    'exitEdit'  => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ],


                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [
                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $location->code
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('label'),
                                    'value' => $location->state // TODO , change State for a proper 'value'
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'models.location.update',
                            'parameters'=> $location->slug

                        ],
                    ]
                ]
            ]
        );
    }

    #[Pure] public function jsonResponse(Location $location): LocationResource
    {
        return new LocationResource($location);
    }
}
