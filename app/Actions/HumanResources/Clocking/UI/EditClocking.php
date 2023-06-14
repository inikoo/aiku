<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\Clocking\UI;

use App\Actions\InertiaAction;
use App\Actions\Inventory\Location\UI\ShowLocation;
use App\Enums\UI\ClockingMachineTabsEnum;
use App\Http\Resources\Inventory\LocationResource;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
use App\Models\Inventory\Location;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditClocking extends InertiaAction
{
    public function handle(Clocking $clocking): Clocking
    {
        return $clocking;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('hr.clocking.edit');
        return $request->user()->hasPermissionTo("hr.working-places.view");
    }

    public function inTenant(Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($request);

        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWorkplace(Workplace $workplace, Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($request);
        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inClockingMachine(ClockingMachine $clockingMachine, Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($request);

        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseInWarehouseArea(Workplace $workplace, ClockingMachine $clockingMachine, Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($request)->withTab(ClockingMachineTabsEnum::values());
        return $this->handle($clocking);
    }

    public function htmlResponse(Clocking $clocking, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('location'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead' => [
                    'title'    => $clocking->slug,
                    'exitEdit' => [
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
                                    'value' => $clocking->type
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'       => 'models.clocking.update',
                            'parameters' => $clocking->slug

                        ],
                    ]
                ]
            ]
        );
    }

    public function jsonResponse(Location $location): LocationResource
    {
        return new LocationResource($location);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowLocation::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '(' . __('editing') . ')'
        );
    }
}
