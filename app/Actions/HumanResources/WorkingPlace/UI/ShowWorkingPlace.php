<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:31:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\WorkingPlace\UI;

use App\Actions\HumanResources\ClockingMachine\UI\IndexClockingMachines;
use App\Actions\InertiaAction;
use App\Actions\UI\HumanResources\HumanResourcesDashboard;
use App\Enums\UI\WorkingPlaceTabsEnum;
use App\Http\Resources\HumanResources\ClockingMachineResource;
use App\Http\Resources\HumanResources\WorkPlaceResource;
use App\Models\HumanResources\Workplace;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowWorkingPlace extends InertiaAction
{
    public function handle(Workplace $workplace): Workplace
    {
        return $workplace;
    }
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('hr.edit');

        return $request->user()->hasPermissionTo("hr.view");
    }

    public function asController(Workplace $workplace, ActionRequest $request): Workplace
    {
        $this->initialisation($request)->withTab(WorkingPlaceTabsEnum::values());
        return $this->handle($workplace);
    }


    public function htmlResponse(Workplace $workplace, ActionRequest $request): Response
    {

        return Inertia::render(
            'HumanResources/WorkingPlace',
            [
                'title'                                 => __('employee'),
                'breadcrumbs'                           => $this->getBreadcrumbs($workplace),
//                'navigation'                            => [
//                    'previous' => $this->getPrevious($workplace, $request),
//                    'next'     => $this->getNext($workplace, $request),
//                ],
                'pageHead'    => [
                    'title' => $workplace->name,
                    'meta'  => [
                        [
                            'name'     => $workplace->name,
                            'leftIcon' => [
                                'icon'    => 'fal fa-id-card',
                                'tooltip' => __('Worker number')
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
                    'navigation' => WorkingPlaceTabsEnum::navigation()
                ],
                WorkingPlaceTabsEnum::SHOWCASE->value => $this->tab == WorkingPlaceTabsEnum::SHOWCASE->value ?
                    fn () => GetWorkingPlaceShowcase::run($workplace)
                    : Inertia::lazy(fn () => GetWorkingPlaceShowcase::run($workplace)),

                WorkingPlaceTabsEnum::CLOCKING_MACHINES->value       => $this->tab == WorkingPlaceTabsEnum::CLOCKING_MACHINES->value ?
                    fn () => ClockingMachineResource::collection(IndexClockingMachines::run($workplace))
                    : Inertia::lazy(fn () => ClockingMachineResource::collection(IndexClockingMachines::run($workplace))),
                WorkingPlaceTabsEnum::CLOCKINGS->value => $this->tab == WorkingPlaceTabsEnum::CLOCKINGS->value
                    ?
                    fn () => ClockingMachineResource::collection(IndexClockingMachines::run($workplace))
                    : Inertia::lazy(fn () => ClockingMachineResource::collection(IndexClockingMachines::run($workplace))),

            ]
        );
        //        ->table(
        //            IndexClockingMachines::make()->tableStructure(
        //                [
        //                    'createLink' => $this->canEdit ? [
        //                        'route' => [
        //                            'name'       => 'hr.working-places.show.clocking-machines.create',
        //                            'parameters' => array_values($this->originalParameters)
        //                        ],
        //                        'label' => __('clocking machines')
        //                    ] : false,
        //                ]
        //            ),
        //        );
    }


    public function jsonResponse(Workplace $workplace): WorkPlaceResource
    {
        return new WorkPlaceResource($workplace);
    }

    public function getBreadcrumbs(Workplace $workplace, $suffix = null): array
    {
        return array_merge(
            (new HumanResourcesDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'hr.working-places.index',
                            ],
                            'label' => __('working places')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'hr.working-places.show',
                                'parameters' => [$workplace->slug]
                            ],
                            'label' => $workplace->name,
                            'icon'  => 'fal fa-bars'
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(Workplace $workplace, ActionRequest $request): ?array
    {
        $previous = Workplace::where('slug', '<', $workplace->slug)->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Workplace $workplace, ActionRequest $request): ?array
    {
        $next = Workplace::where('slug', '>', $workplace->slug)->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Workplace $workplace, string $routeName): ?array
    {
        if (!$workplace) {
            return null;
        }

        return match ($routeName) {
            'hr.working-places.show' => [
                'label' => $workplace->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'workPlace' => $workplace->slug
                    ]

                ]
            ]
        };
    }
}
