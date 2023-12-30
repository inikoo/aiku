<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 11:34:34 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\Clocking\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\HumanResources\ClockingMachine\UI\ShowClockingMachine;
use App\Actions\HumanResources\Workplace\UI\ShowWorkplace;
use App\Actions\InertiaAction;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Enums\UI\ClockingTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Clocking $clocking
 */
class ShowClocking extends InertiaAction
{
    public function handle(Clocking $clocking): Clocking
    {
        return $clocking;
    }
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo('hr.edit');
        $this->canDelete = $request->user()->hasPermissionTo('hr.edit');
        return $request->user()->hasPermissionTo("hr.view");
    }


    public function inTenant(Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($request)->withTab(ClockingTabsEnum::values());
        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWorkplace(Workplace $workplace, Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($request)->withTab(ClockingTabsEnum::values());
        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inClockingMachine(ClockingMachine $clockingMachine, Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($request)->withTab(ClockingTabsEnum::values());
        return $this->handle($clocking);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inWorkplaceInClockingMachine(Workplace $workplace, ClockingMachine $clockingMachine, Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($request)->withTab(ClockingTabsEnum::values());
        return $this->handle($clocking);
    }

    public function htmlResponse(Clocking $clocking, ActionRequest $request): Response
    {
        return Inertia::render(
            'HumanResources/Clocking',
            [
                'title'       => __('clocking'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($clocking, $request),
                    'next'     => $this->getNext($clocking, $request),
                ],
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-clock'],
                            'title' => __('clocking')
                        ],
                    'title'   => $clocking->slug,
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : false,
                        $this->canDelete ?
                            match ($request->route()->getName()) {
                                'grp.hr.clockings.show' => [
                                    'type'  => 'button',
                                    'style' => 'delete',
                                    'route' => [
                                        'name'       => 'grp.hr.clockings.remove',
                                        'parameters' => $request->route()->originalParameters()
                                    ],

                                ],
                                'grp.hr.working-places.show.clockings.show' => [
                                    'type'  => 'button',
                                    'style' => 'delete',
                                    'route' => [
                                        'name'       => 'grp.hr.working-places.show.clockings.remove',
                                        'parameters' => $request->route()->originalParameters()
                                    ],
                                ],
                                'grp.hr.working-places.show.clocking-machines.show.clockings.show' => [
                                    'type'  => 'button',
                                    'style' => 'delete',
                                    'route' => [
                                        'name'       => 'grp.hr.working-places.show.clocking-machines.show.clockings.remove',
                                        'parameters' => $request->route()->originalParameters()
                                    ]
                                ]
                            } : false
                    ]
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => ClockingTabsEnum::navigation()

                ],

                ClockingTabsEnum::SHOWCASE->value => $this->tab == ClockingTabsEnum::SHOWCASE->value ?
                    fn () => GetClockingShowcase::run($clocking)
                    : Inertia::lazy(fn () => GetClockingShowcase::run($clocking)),

                ClockingTabsEnum::HISTORY->value => $this->tab == ClockingTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($clocking))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($clocking)))
            ]
        )->table(IndexHistory::make()->tableStructure());
    }


    public function jsonResponse(Clocking $clocking): JsonResource
    {
        return new JsonResource($clocking);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (Clocking $clocking, array $routeParameters, string $suffix) {
            return [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('clockings')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $clocking->slug,
                        ],

                    ],
                    'suffix'=> $suffix
                ],
            ];
        };

        return match ($routeName) {
            'grp.hr.clockings.show' =>
            array_merge(
                ShowHumanResourcesDashboard::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $routeParameters['clocking'],
                    [
                        'index' => [
                            'name'       => 'grp.hr.clockings.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'grp.hr.clockings.show',
                            'parameters' => [$routeParameters['clocking']->slug]
                        ]
                    ],
                    $suffix
                ),
            ),
            'grp.hr.working-places.show.clockings.show' => array_merge(
                (new ShowWorkplace())->getBreadcrumbs($routeParameters['workplace']),
                $headCrumb(
                    $routeParameters['clocking'],
                    [
                        'index' => [
                            'name'       => 'grp.hr.working-places.show.clockings.index',
                            'parameters' => [
                                $routeParameters['workplace']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'grp.hr.working-places.show.clockings.show',
                            'parameters' => [
                                $routeParameters['workplace']->slug,
                                $routeParameters['clocking']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.hr.clocking-machines.show.clockings.show' => array_merge(
                (new ShowClockingMachine())->getBreadcrumbs(
                    'grp.hr.clocking-machines.show',
                    [
                       'clockingMachine' => $routeParameters['clockingMachine']
                    ]
                ),
                $headCrumb(
                    $routeParameters['clocking'],
                    [
                        'index' => [
                            'name'       => 'grp.hr.clocking-machines.show.clockings.index',
                            'parameters' => [
                                $routeParameters['clockingMachine']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'grp.hr.clocking-machines.show.clockings.show',
                            'parameters' => [
                                $routeParameters['clockingMachine']->slug,
                                $routeParameters['clocking']->slug
                            ]
                        ]
                    ],
                    $suffix
                ),
            ),
            'grp.hr.working-places.show.clocking-machines.show.clockings.show' => array_merge(
                (new ShowClockingMachine())->getBreadcrumbs(
                    'grp.hr.working-places.show.clocking-machines.show',
                    [
                      'workplace'       => $routeParameters['workplace'],
                      'clockingMachine' => $routeParameters['clockingMachine'],
                    ]
                ),
                $headCrumb(
                    $routeParameters['clocking'],
                    [
                        'index' => [
                            'name'       => 'grp.hr.working-places.show.clocking-machines.show.clockings.index',
                            'parameters' => [
                                $routeParameters['workplace']->slug,
                                $routeParameters['clockingMachine']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'grp.hr.working-places.show.clocking-machines.show.clockings.show',
                            'parameters' => [
                                $routeParameters['workplace']->slug,
                                $routeParameters['clockingMachine']->slug,
                                $routeParameters['clocking']->slug
                            ]
                        ]
                    ],
                    $suffix
                ),
            ),

            default => []
        };
    }

    public function getPrevious(Clocking $clocking, ActionRequest $request): ?array
    {
        $previous=Clocking::where('slug', '<', $clocking->slug)->when(true, function ($query) use ($clocking, $request) {
            switch ($request->route()->getName()) {
                case 'grp.hr.working-places.show.clockings.show':
                    $query->where('clockings.workplace_id', $clocking->workplace_id);
                    break;
                case 'grp.hr.working-places.show.clocking-machines.show.clockings.show':
                case 'grp.hr.clocking-machines.show.clockings.show':
                    $query->where('clockings.clocking_machine_id', $clocking->clocking_machine_id);
                    break;

            }
        })->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Clocking $clocking, ActionRequest $request): ?array
    {
        $next = Clocking::where('slug', '>', $clocking->slug)->when(true, function ($query) use ($clocking, $request) {
            switch ($request->route()->getName()) {
                case 'grp.hr.working-places.show.clockings.show':
                    $query->where('clockings.workplace_id', $clocking->workplace_id);
                    break;
                case 'grp.hr.working-places.show.clocking-machines.show.clockings.show':
                case 'grp.hr.clocking-machines.show.clockings.show':
                    $query->where('clockings.clocking_machine_id', $clocking->clocking_machine_id);
                    break;

            }
        })->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Clocking $clocking, string $routeName): ?array
    {
        if(!$clocking) {
            return null;
        }
        return match ($routeName) {
            'grp.hr.clockings.show'=> [
                'label'=> $clocking->slug,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'clocking'  => $clocking->slug
                    ]

                ]
            ],
            'grp.hr.clocking-machines.show.clockings.show' => [
                'label'=> $clocking->slug,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'clockingMachine'   => $clocking->clockingMachine->slug,
                        'clocking'          => $clocking->slug
                    ]

                ]
            ],
            'grp.hr.working-places.show.clockings.show'=> [
                'label'=> $clocking->slug,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'workplace' => $clocking->workplace->slug,
                        'clocking'  => $clocking->slug
                    ]

                ]
            ],
            'grp.hr.working-places.show.clocking-machines.show.clockings.show' => [
                'label'=> $clocking->slug,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'workplace'         => $clocking->workplace->slug,
                        'clockingMachine'   => $clocking->clockingMachine->slug,
                        'clocking'          => $clocking->slug
                    ]

                ]
            ]
        };
    }

}
