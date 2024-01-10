<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:34:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\ClockingMachine\UI;

use App\Actions\InertiaOrganisationAction;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateClockingMachine extends InertiaOrganisationAction
{
    public function handle(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('new clocking machine'),
                'pageHead'    => [
                    'title'        => __('new clocking machine'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'grp.org.hr.workplaces.show.clocking-machines.index',
                            'parameters' => $request->route()->originalParameters()
                        ],
                    ]

                ],
                'formData'    => [
                    'blueprint' => [
                        [
                            'title'  => __('create clocking machine'),
                            'fields' => [
                                'code' => [
                                    'type'        => 'input',
                                    'label'       => __('code'),
                                ],
                            ]
                        ],
                    ],
                    'route'     => [
                        'name'      => 'grp.models.working-place.clocking-machine.store',
                        'arguments' => [
                            'organisation' => $request->route()->parameter('organisation')->slug,
                            'workplace'    => $request->route()->parameter('workplace')->slug
                        ]
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.clocking-machines.{$this->organisation->slug}.edit");
    }


    public function asController(Organisation $organisation, Workplace $workplace, ActionRequest $request): Response
    {
        $this->initialisation($organisation, $request);

        return $this->handle($request);
    }



    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {

        return array_merge(
            IndexClockingMachines::make()->getBreadcrumbs(
                routeName: preg_replace('/create$/', 'index', $routeName),
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating clocking machines'),
                    ]
                ]
            ]
        );
    }
}
