<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Jun 2023 11:39:33 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition\UI;

use App\Actions\Helpers\History\IndexHistories;
use App\Actions\HumanResources\Employee\UI\IndexEmployees;
use App\Actions\InertiaAction;
use App\Actions\UI\HumanResources\HumanResourcesDashboard;
use App\Enums\UI\JobPositionTabsEnum;
use App\Http\Resources\HumanResources\EmployeeResource;
use App\Http\Resources\HumanResources\JobPositionResource;
use App\Http\Resources\SysAdmin\HistoryResource;
use App\Models\HumanResources\JobPosition;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowJobPosition extends InertiaAction
{
    public function handle(JobPosition $jobPosition): JobPosition
    {
        return $jobPosition;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('hr.edit');

        return $request->user()->hasPermissionTo("hr.view");
    }

    public function asController(JobPosition $jobPosition, ActionRequest $request): JobPosition
    {
        $this->initialisation($request)->withTab(JobPositionTabsEnum::values());

        return $this->handle($jobPosition);
    }

    public function htmlResponse(JobPosition $jobPosition, ActionRequest $request): Response
    {
        return Inertia::render(
            'HumanResources/JobPosition',
            [
                'title'       => __('position'),
                'breadcrumbs' => $this->getBreadcrumbs($jobPosition),
                'navigation'  => [
                    'previous' => $this->getPrevious($jobPosition, $request),
                    'next'     => $this->getNext($jobPosition, $request),
                ],
                'pageHead'    => [
                    'title' => $jobPosition->name,

                    'edit' => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => JobPositionTabsEnum::navigation()
                ],

                JobPositionTabsEnum::SHOWCASE->value => $this->tab == JobPositionTabsEnum::SHOWCASE->value ?
                fn () => GetJobPositionShowcase::run($jobPosition)
                : Inertia::lazy(fn () => GetJobPositionShowcase::run($jobPosition)),

                JobPositionTabsEnum::EMPLOYEES->value       => $this->tab == JobPositionTabsEnum::EMPLOYEES->value ?
                fn () => EmployeeResource::collection(
                    IndexEmployees::run(
                        prefix: 'employees'
                    )
                )
                : Inertia::lazy(fn () => EmployeeResource::collection(
                    IndexEmployees::run(
                        prefix: 'employees'
                    )
                )),

//               JobPositionTabsEnum::ROLES->value => $this->tab == JobPositionTabsEnum::ROLES->value
//        ?
//        fn () => RoleResource::collection(
//            IndexRoles::run(
//                parent: $jobPosition,
//                prefix: 'roles'
//            )
//        )
//        : Inertia::lazy(fn () => RoleResource::collection(
//            IndexRoles::run(
//                parent: $this->warehouse,
//                prefix: 'roles'
//            )
//        )),


                JobPositionTabsEnum::HISTORY->value => $this->tab == JobPositionTabsEnum::HISTORY->value ?
                fn () => HistoryResource::collection(IndexHistories::run($jobPosition))
                : Inertia::lazy(fn () => HistoryResource::collection(IndexHistories::run($jobPosition)))
            ]
        );
    }


    public function jsonResponse(JobPosition $jobPosition): JobPositionResource
    {
        return new JobPositionResource($jobPosition);
    }

    public function getBreadcrumbs(JobPosition $jobPosition, $suffix = null): array
    {
        return array_merge(
            (new HumanResourcesDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'hr.job-positions.index',
                            ],
                            'label' => __('positions')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'hr.job-positions.show',
                                'parameters' => [$jobPosition->slug]
                            ],
                            'label' => $jobPosition->name,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(JobPosition $jobPosition, ActionRequest $request): ?array
    {
        $previous = JobPosition::where('slug', '<', $jobPosition->slug)->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(JobPosition $jobPosition, ActionRequest $request): ?array
    {
        $next = JobPosition::where('slug', '>', $jobPosition->slug)->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?JobPosition $jobPosition, string $routeName): ?array
    {
        if (!$jobPosition) {
            return null;
        }

        return match ($routeName) {
            'hr.job-positions.show' => [
                'label' => $jobPosition->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'jobPosition' => $jobPosition->slug
                    ]

                ]
            ]
        };
    }
}
