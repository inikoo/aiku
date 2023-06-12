<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:13:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\ClockingMachine\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\HumanResources\HumanResourcesDashboard;
use App\Enums\UI\ClockingMachineTabsEnum;
use App\Enums\UI\EmployeeTabsEnum;
use App\Http\Resources\HumanResources\ClockingMachineResource;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\ClockingMachine;
use App\Models\HumanResources\Employee;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowClockingMachine extends InertiaAction
{
    public function handle(ClockingMachine $clockingMachine): ClockingMachine
    {
        return $clockingMachine;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('hr.edit');

        return $request->user()->hasPermissionTo("hr.view");
    }

    public function asController(ClockingMachine $clockingMachine, ActionRequest $request): ClockingMachine
    {
        $this->initialisation($request)->withTab(ClockingMachineTabsEnum::values());
        return $this->handle($clockingMachine);
    }

    public function htmlResponse(ClockingMachine $clockingMachine, ActionRequest $request): Response
    {
        return Inertia::render(
            'HumanResources/Employee',
            [
                'title'                                 => __('employee'),
                'breadcrumbs'                           => $this->getBreadcrumbs($clockingMachine),
                'navigation'                            => [
                    'previous' => $this->getPrevious($clockingMachine, $request),
                    'next'     => $this->getNext($clockingMachine, $request),
                ],
                'pageHead'    => [
                    'title' => $clockingMachine->slug,
                    'meta'  => [
                        [
                            'name'     => $clockingMachine->code,
                            'leftIcon' => [
                                'icon'    => 'fal fa-id-card',
                                'tooltip' => __('Code')
                            ]
                        ],
                    ],
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => ClockingMachineTabsEnum::navigation()
                ]
            ]
        );
    }


    public function jsonResponse(ClockingMachine $clockingMachine): ClockingMachineResource
    {
        return new ClockingMachineResource($clockingMachine);
    }

    public function getBreadcrumbs(ClockingMachine $clockingMachine, $suffix = null): array
    {
        return array_merge(
            (new HumanResourcesDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'hr.clocking-machines.index',
                            ],
                            'label' => __('clocking machines')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'hr.clocking-machines.show',
                                'parameters' => [$clockingMachine->slug]
                            ],
                            'label' => $clockingMachine->code,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(ClockingMachine $clockingMachine, ActionRequest $request): ?array
    {
        $previous = ClockingMachine::where('slug', '<', $clockingMachine->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(ClockingMachine $clockingMachine, ActionRequest $request): ?array
    {
        $next = ClockingMachine::where('slug', '>', $clockingMachine->slug)->orderBy('slug')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?ClockingMachine $clockingMachine, string $routeName): ?array
    {
        if(!$clockingMachine) {
            return null;
        }
        return match ($routeName) {
            'hr.clocking-machines.show'=> [
                'label'=> $clockingMachine->code,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'clocking-machine'=> $clockingMachine->slug
                    ]

                ]
            ]
        };
    }
}
