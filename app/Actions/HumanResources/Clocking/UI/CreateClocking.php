<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 11 Apr 2023 08:24:46 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\Clocking\UI;

use App\Actions\InertiaAction;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\Workplace;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateClocking extends InertiaAction
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
                'title'    => __('new clocking'),
                'pageHead' => [
                    'title'        => __('new clocking'),
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'hr.working-places.show.clockings.index',
                                'parameters' => array_values($this->originalParameters)
                            ],
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [
                                'generator_id' => [
                                    'type'        => 'select',
                                    'label'       => __('employee'),
                                    'placeholder' => 'Select A Employee',
                                    'options'     => Options::forModels(Employee::class, 'contact_name', 'id'),
                                    'required'    => true,
                                    'searchable'  => true
                                ],
                                'date' => [
                                    'type'     => 'date',
                                    'label'    => __('date'),
                                    'required' => true
                                ],
                                'time' => [
                                    'type'     => 'time',
                                    'label'    => __('time'),
                                    'required' => true
                                ],
                            ]
                        ],


                    ],
                    'route' => match ($this->routeName) {
                        'hr.working-places.show.clockings.create' => [
                            'name'      => 'models.working-place.clocking.store',
                            'arguments' => [$request->route()->parameters['workplace']->slug]
                        ],
                        default => [
                            'name'      => 'models.clocking-machine.clocking.store',
                            'arguments' => [
                                $request->route()->parameters['clockingMachine']->slug
                            ]
                        ]
                    }
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('hr');
    }


    public function inWorkplaceInClockingMachine(Workplace $workplace, ClockingMachine $clockingMachine, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($request);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexClockings::make()->getBreadcrumbs(
                routeName: preg_replace('/create$/', 'index', $routeName),
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating clocking'),
                    ]
                ]
            ]
        );
    }
}
