<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:34:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\ClockingMachine\UI;

use App\Actions\OrgAction;
use App\Enums\HumanResources\ClockingMachine\ClockingMachineTypeEnum;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateClockingMachine extends OrgAction
{
    public function handle(ActionRequest $request): Response
    {
        // dd($request->route()->parameter('workplace')->id);
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
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
                                'name' => [
                                    'type'        => 'input',
                                    'label'       => __('name'),
                                ],
                                'type' => [
                                    'type'        => 'select',
                                    'options'     => Options::forEnum(ClockingMachineTypeEnum::class),
                                    'label'       => __('type'),
                                ],
                            ]
                        ],
                    ],
                    'route'     => [
                        'name'       => 'grp.models.org.workplaces.clocking-machines.store',
                        'parameters' => [
                            'organisation' => $request->route()->parameter('organisation')->id,
                            'workplace'    => $request->route()->parameter('workplace')->id
                        ]
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        // for testing
        $this->canEdit   = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view");
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
