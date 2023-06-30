<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:31:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\WorkingPlace\UI;

use App\Actions\Helpers\History\IndexHistories;
use App\Actions\InertiaAction;
use App\Actions\HumanResources\Clocking\UI\IndexClockings;
use App\Actions\HumanResources\ClockingMachine\UI\IndexClockingMachines;
use App\Actions\Traits\WithElasticsearch;
use App\Actions\UI\HumanResources\HumanResourcesDashboard;
use App\Enums\UI\WorkingPlaceTabsEnum;
use App\Http\Resources\HumanResources\ClockingResource;
use App\Http\Resources\HumanResources\ClockingMachineResource;
use App\Http\Resources\HumanResources\WorkPlaceResource;
use App\Http\Resources\SysAdmin\HistoryResource;
use App\Models\HumanResources\Workplace;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowWorkingPlace extends InertiaAction
{
    use WithElasticsearch;

    public function handle(Workplace $workplace): Workplace
    {
        return $workplace;
    }
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->can('hr.edit');
        $this->canDelete = $request->user()->can('hr.edit');
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
                'title'                            => __('working place'),
                'breadcrumbs'                      => $this->getBreadcrumbs($workplace),
                'navigation'                       => [
                    'previous' => $this->getPrevious($workplace, $request),
                    'next'     => $this->getNext($workplace, $request),
                ],
                'pageHead'                         => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'building'],
                            'title' => __('working place')
                        ],
                    'title'   => $workplace->name,
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                        $this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'hr.working-places.remove',
                                'parameters' => $request->route()->originalParameters()
                            ]

                        ] : false
                    ],
                    'meta' => [
                        [
                            'name'     => trans_choice('clocking machine|clocking machines', 0 /*$workplace->stats->number_clocking_machines */),
                            'number'   => 0/* $workplace->stats->number_clocking_machines */,
                            'href'     => [
                                'hr.working-places.show.clocking-machines.index',
                                $workplace->slug
                            ],
                            'leftIcon' => [
                                'icon'    => [ 'fal', 'chess-clock'],
                                'tooltip' => __('clocking machines')
                            ]
                        ],
                        [
                            'name'     => trans_choice('clocking|clockings', 0 /* $workplace->stats->number_clockings */),
                            'number'   => 0/* $workplace->stats->number_clockings */,
                            'href'     => [
                                'hr.working-places.show.clockings.index',
                                $workplace->slug
                            ],
                            'leftIcon' => [
                                'icon'    => [ 'fal', 'clock'],
                                'tooltip' => __('clockings')
                            ]
                        ]
                    ]

                ],
                'tabs'                             => [

                    'current'    => $this->tab,
                    'navigation' => WorkingPlaceTabsEnum::navigation(),

                ],
                WorkingPlaceTabsEnum::SHOWCASE->value => $this->tab == WorkingPlaceTabsEnum::SHOWCASE->value ?
                    fn () => GetWorkingPlaceShowcase::run($workplace)
                    : Inertia::lazy(fn () => GetWorkingPlaceShowcase::run($workplace)),

                WorkingPlaceTabsEnum::CLOCKINGS->value       => $this->tab == WorkingPlaceTabsEnum::CLOCKINGS->value ?
                    fn () => ClockingResource::collection(IndexClockings::run(
                        parent: $workplace,
                        prefix: 'clockings'
                    ))
                    : Inertia::lazy(fn () => ClockingResource::collection(IndexClockings::run(
                        parent: $workplace,
                        prefix: 'clockings'
                    ))),
                WorkingPlaceTabsEnum::CLOCKING_MACHINES->value => $this->tab == WorkingPlaceTabsEnum::CLOCKING_MACHINES->value
                    ?
                    fn () => ClockingMachineResource::collection(IndexClockingMachines::run(
                        parent: $workplace,
                        prefix: 'clocking_machines'
                    ))
                    : Inertia::lazy(fn () => ClockingMachineResource::collection(IndexClockingMachines::run(
                        parent: $workplace,
                        prefix: 'clocking_machines'
                    ))),

                WorkingPlaceTabsEnum::HISTORY->value => $this->tab == WorkingPlaceTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistories::run($workplace))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistories::run($workplace)))
            ]
        )->table(IndexClockings::make()->tableStructure(
            /* modelOperations:[
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'hr.working-places.show.clockings.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('clocking')
                    ] : false,
                ],
            prefix: 'clockings' */
        ))->table(
            IndexClockingMachines::make()->tableStructure(
                /* modelOperations: [
                        'createLink' => $this->canEdit ? [
                            'route' => [
                                'name'       => 'hr.working-places.show.clocking-machines.create',
                                'parameters' => array_values($this->originalParameters)
                            ],
                            'label' => __('clocking machine')
                        ] : false,
                    ],
                prefix: 'clocking_machines' */
            )
        )->table(IndexHistories::make()->tableStructure());
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
                            'label' => __('working place'),
                            'icon'  => 'fal fa-bars'
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'hr.working-places.show',
                                'parameters' => [$workplace->slug]
                            ],
                            'label' => $workplace->slug,
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
                        'workplace' => $workplace->slug
                    ]
                ]
            ]
        };
    }
}
