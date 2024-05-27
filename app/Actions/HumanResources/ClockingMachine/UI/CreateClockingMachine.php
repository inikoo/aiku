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
    public function handle(Organisation|Workplace $parent, ActionRequest $request): Response
    {
        // dd($parent);
        $workplaces = [];
        if ($parent instanceof Organisation) {
            $workplaces = $parent->workplaces()->get()->map(function ($workplace) {
                return ['value' => $workplace->id, 'label' => $workplace->name];
            })->toArray();
        }

        $fields = [
            'name' => [
                'type'  => 'input',
                'label' => __('name'),
            ],
            'type' => [
                'type'    => 'select',
                'options' => Options::forEnum(ClockingMachineTypeEnum::class),
                'label'   => __('type'),
            ],
        ];

        if ($parent instanceof Organisation) {
            $fields['workplace_id'] = [
                'type'    => 'select',
                'options' => $workplaces,
                'label'   => __('workplace'),
            ];
        }
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
                        'route' =>
                            match (class_basename($parent)) {
                                'Workplace' => [
                                    'name'       => 'grp.org.hr.workplaces.show.clocking_machines.index',
                                    'parameters' => $request->route()->originalParameters()
                                ],
                                default => [
                                    'name'       => 'grp.org.hr.clocking_machines.index',
                                    'parameters' => $request->route()->originalParameters()
                                ]
                            }


                    ]

                ],
                'formData'    => [
                    'blueprint' => [
                        [
                            'fields' => $fields
                        ],
                    ],
                    'route'     =>
                        match (class_basename($parent)) {
                            'Workplace' =>
                            [
                                'name'       => 'grp.models.workplace.clocking_machine.store',
                                'parameters' => $parent->id
                            ],
                            default =>
                            [
                                'name'       => 'grp.models.org.clocking-machine.store',
                                'parameters' => $parent->id
                            ]
                        }


                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view");
    }


    public function inOrganisation(Organisation $organisation, ActionRequest $request): Response
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, $request);
    }

    public function asController(Organisation $organisation, Workplace $workplace, ActionRequest $request): Response
    {
        $this->initialisation($organisation, $request);

        return $this->handle($workplace, $request);
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
                        'label' => __('Creating clocking machines'),
                    ]
                ]
            ]
        );
    }
}
