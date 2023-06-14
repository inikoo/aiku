<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:13:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\ClockingMachine\UI;

use App\Actions\Helpers\History\IndexHistories;
use App\Actions\HumanResources\Clocking\UI\IndexClockings;
use App\Actions\InertiaAction;
use App\Actions\UI\HumanResources\HumanResourcesDashboard;
use App\Enums\UI\ClockingMachineTabsEnum;
use App\Http\Resources\HumanResources\ClockingMachineResource;
use App\Http\Resources\HumanResources\ClockingResource;
use App\Http\Resources\SysAdmin\HistoryResource;
use App\Models\HumanResources\ClockingMachine;
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
        $this->canEdit = $request->user()->can('hr.clocking-machines.edit');

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
            'HumanResources/ClockingMachine',
            [
                'title'                                 => __('clocking machine'),
                'breadcrumbs'                           => $this->getBreadcrumbs($clockingMachine),
                'navigation'                            => [
                    'previous' => $this->getPrevious($clockingMachine, $request),
                    'next'     => $this->getNext($clockingMachine, $request),
                ],
                'pageHead'                              => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'clock'],
                            'title' => __('clocking machine')
                        ],
                    'title' => $clockingMachine->slug,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                    'meta'  => [
                        [
                            'name'     => trans_choice('clocking|clockings', 0/*$clockingMachine->stats->number_clockings*/),
                            'number'   => 0/*$clockingMachine->stats->number_locations*/,
                            'href'     =>
                                match ($this->routeName) {
                                    'hr.working-places.show.clocking-machines.show' => [
                                        'hr.working-places.show.clocking-machines.show.clockings.index',
                                        [$clockingMachine->workplace->slug, $clockingMachine->slug]
                                    ],
                                    default => [
                                        'hr.working-places.show.clockings.index',
                                        $clockingMachine->slug
                                    ]
                                }


                            ,
                            'leftIcon' => [
                                'icon'    => ['fal', 'clock'],
                                'tooltip' => __('clockings')
                            ]
                        ]
                    ],

                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => ClockingMachineTabsEnum::navigation()
                ],
                ClockingMachineTabsEnum::SHOWCASE->value => $this->tab == ClockingMachineTabsEnum::SHOWCASE->value ?
                    fn () => GetClockingMachineShowcase::run($clockingMachine)
                    : Inertia::lazy(fn () => GetClockingMachineShowcase::run($clockingMachine)),

                ClockingMachineTabsEnum::CLOCKINGS->value => $this->tab == ClockingMachineTabsEnum::CLOCKINGS->value ?
                    fn () => ClockingResource::collection(IndexClockings::run($clockingMachine))
                    : Inertia::lazy(fn () => ClockingResource::collection(IndexClockings::run($clockingMachine))),

                ClockingMachineTabsEnum::HISTORY->value => $this->tab == ClockingMachineTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistories::run($clockingMachine))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistories::run($clockingMachine)))
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
