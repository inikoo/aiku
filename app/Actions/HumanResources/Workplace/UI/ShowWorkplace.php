<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Workplace\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\HumanResources\Clocking\UI\IndexClockings;
use App\Actions\HumanResources\ClockingMachine\UI\IndexClockingMachines;
use App\Actions\OrgAction;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Actions\UI\HumanResources\ShowHumanResourcesDashboard;
use App\Enums\UI\HumanResources\WorkplaceTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\HumanResources\ClockingMachineResource;
use App\Http\Resources\HumanResources\ClockingResource;
use App\Http\Resources\HumanResources\WorkplaceResource;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowWorkplace extends OrgAction
{
    use WithActionButtons;

    public function handle(Workplace $workplace): Workplace
    {
        return $workplace;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, Workplace $workplace, ActionRequest $request): Workplace
    {
        $this->initialisation($organisation, $request)->withTab(WorkplaceTabsEnum::values());
        return $this->handle($workplace);
    }

    public function htmlResponse(Workplace $workplace, ActionRequest $request): Response
    {

        return Inertia::render(
            'Org/HumanResources/Workplace',
            [
                'title'                            => __('working place'),
                'breadcrumbs'                      => $this->getBreadcrumbs($request->route()->originalParameters()),
                'navigation'                       => [
                    'previous' => $this->getPrevious($workplace, $request),
                    'next'     => $this->getNext($workplace, $request),
                ],
                'pageHead'                         => [
                    'icon'        =>
                        [
                            'icon'  => ['fal', 'building'],
                            'title' => __('working place')
                        ],
                    'title'       => $workplace->name,
                    'iconActions' => [
                        $this->canDelete ? $this->getDeleteActionIcon($request) : null,
                        $this->canEdit ? $this->getEditActionIcon($request) : null,
                    ],


                    'meta' => [
                        [
                            'label'    => trans_choice('clocking machine|clocking machines', $workplace->stats->number_clocking_machines),
                            'number'   => $workplace->stats->number_clocking_machines,
                            'href'     => [
                                'name'       => 'grp.org.hr.workplaces.show.clocking-machines.index',
                                'parameters' => [$this->organisation->slug, $workplace->slug]
                            ],
                            'leftIcon' => [
                                'icon'    => ['fal', 'chess-clock'],
                                'tooltip' => __('clocking machines')
                            ]
                        ],
                        [
                            'label'    => trans_choice('clocking|clockings', $workplace->stats->number_clockings),
                            'number'   => $workplace->stats->number_clockings,
                            'href'     => [
                                'name'       => 'grp.org.hr.workplaces.show.clockings.index',
                                'parameters' => [$this->organisation->slug, $workplace->slug]
                            ],
                            'leftIcon' => [
                                'icon'    => ['fal', 'clock'],
                                'tooltip' => __('clockings')
                            ]
                        ]
                    ]

                ],
                'tabs'                             => [

                    'current'    => $this->tab,
                    'navigation' => WorkplaceTabsEnum::navigation(),

                ],
                WorkplaceTabsEnum::SHOWCASE->value => $this->tab == WorkplaceTabsEnum::SHOWCASE->value ?
                    fn () => GetWorkplaceShowcase::run($workplace)
                    : Inertia::lazy(fn () => GetWorkplaceShowcase::run($workplace)),

                WorkplaceTabsEnum::CLOCKINGS->value         => $this->tab == WorkplaceTabsEnum::CLOCKINGS->value
                    ?
                    fn () => ClockingResource::collection(
                        IndexClockings::run(
                            parent: $workplace,
                            prefix: 'clockings'
                        )
                    )
                    : Inertia::lazy(fn () => ClockingResource::collection(
                        IndexClockings::run(
                            parent: $workplace,
                            prefix: 'clockings'
                        )
                    )),
                WorkplaceTabsEnum::CLOCKING_MACHINES->value => $this->tab == WorkplaceTabsEnum::CLOCKING_MACHINES->value
                    ?
                    fn () => ClockingMachineResource::collection(
                        IndexClockingMachines::run(
                            parent: $workplace,
                            prefix: 'clocking_machines'
                        )
                    )
                    : Inertia::lazy(fn () => ClockingMachineResource::collection(
                        IndexClockingMachines::run(
                            parent: $workplace,
                            prefix: 'clocking_machines'
                        )
                    )),

                WorkplaceTabsEnum::HISTORY->value => $this->tab == WorkplaceTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($workplace))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($workplace)))
            ]
        )->table(
            IndexClockings::make()->tableStructure(
                parent: $workplace,
                /* modelOperations:[
                        'createLink' => $this->canEdit ? [
                            'route' => [
                                'name'       => 'grp.org.hr.workplaces.show.clockings.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                            'label' => __('clocking')
                        ] : false,
                    ],
                prefix: 'clockings' */
            )
        )->table(
            IndexClockingMachines::make()->tableStructure(
                parent: $workplace,
                 modelOperations: [
                        'createLink' => [
                            [
                            'route' => [
                                'name'       => 'grp.org.hr.workplaces.show.clocking-machines.create',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => __('create clocking machine')
                            ]
                        ]
                    ],
                prefix: 'clocking_machines'
            )
        )->table(IndexHistory::make()->tableStructure());
    }

    public function jsonResponse(Workplace $workplace): WorkplaceResource
    {
        return new WorkplaceResource($workplace);
    }

    public function getBreadcrumbs($routeParameters, $suffix = null): array
    {
        // dd($routeParameters['workplace']);
        $workplace = Workplace::where('slug', $routeParameters['workplace'])->first();
        return array_merge(
            (new ShowHumanResourcesDashboard())->getBreadcrumbs($routeParameters),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.hr.workplaces.index',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('working place'),
                            'icon'  => 'fal fa-bars'
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.hr.workplaces.show',
                                'parameters' => $routeParameters
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
        $previous = Workplace::where('slug', '<', $workplace->slug)
            ->where('organisation_id', $this->organisation->id)
            ->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Workplace $workplace, ActionRequest $request): ?array
    {
        $next = Workplace::where('slug', '>', $workplace->slug)
            ->where('organisation_id', $this->organisation->id)
            ->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Workplace $workplace, string $routeName): ?array
    {
        if (!$workplace) {
            return null;
        }

        return match ($routeName) {
            'grp.org.hr.workplaces.show' => [
                'label' => $workplace->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'workplace'    => $workplace->slug
                    ]
                ]
            ]
        };
    }
}
