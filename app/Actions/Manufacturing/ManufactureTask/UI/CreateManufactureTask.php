<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

 namespace App\Actions\Manufacturing\ManufactureTask\UI;

use App\Actions\OrgAction;
use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardAllowanceTypeEnum;
use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardTermsEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStockStatusEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialUnitEnum;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateManufactureTask extends OrgAction
{
    protected Production|Organisation $parent;

    public function handle(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'       => __('new manufacture task'),
                'pageHead'    => [
                    'title'        => __('new manufacture task'),
                    'icon'         => [
                        'title' => __('create manufacture task'),
                        'icon'  => 'fal fa-industry'
                    ],
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'grp.org.productions.show.crafts.manufacture_tasks.index',
                                'parameters' => $request->route()->originalParameters()
                            ],
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('create raw material'),
                            'fields' => [
                                'code' => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'name' => [
                                    'type'     => 'input',
                                    'label'    => __('name'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'task_materials_cost' => [
                                    'type'     => 'input',
                                    'label'    => __('task materials cost'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'task_energy_cost' => [
                                    'type'     => 'input',
                                    'label'    => __('task energy cost'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'task_other_cost' => [
                                    'type'     => 'input',
                                    'label'    => __('task other cost'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'task_work_cost' => [
                                    'type'     => 'input',
                                    'label'    => __('task work cost'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'status' => [
                                    'type'     => 'toggle',
                                    'label'    => __('status'),
                                    'value'    => true,
                                    'required' => true
                                ],
                                'task_lower_target' => [
                                    'type'     => 'input',
                                    'label'    => __('task lower target'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'task_upper_target' => [
                                    'type'     => 'input',
                                    'label'    => __('task upper target'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'operative_reward_terms' => [
                                    'type'     => 'select',
                                    'options'   => ManufactureTaskOperativeRewardTermsEnum::values(),
                                    'label'    => __('operative reward terms'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'operative_reward_allowance_type' => [
                                    'type'     => 'select',
                                    'options'   => ManufactureTaskOperativeRewardAllowanceTypeEnum::values(),
                                    'label'    => __('operative reward allowance type'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'operative_reward_amount' => [
                                    'type'     => 'input',
                                    'label'    => __('operative reward amount'),
                                    'value'    => '',
                                    'required' => true
                                ],

                            ]
                        ]
                    ],
                    'route'      => [
                        'name'       => 'grp.models.production.manufacture_tasks.store',
                        'parameters' => [$this->parent->id]
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Organisation) {
            $this->canEdit = $request->user()->hasPermissionTo('org-supervisor.'.$this->organisation->id);

            return $request->user()->hasAnyPermission(
                [
                    'productions-view.'.$this->organisation->id,
                    'org-supervisor.'.$this->organisation->id
                ]
            );
        }

        $this->canEdit = $request->user()->hasPermissionTo("productions_rd.{$this->production->id}.edit");

        return $request->user()->hasPermissionTo("productions_rd.{$this->production->id}.view");
    }


    public function inOrganisation(Organisation $organisation, ActionRequest $request): Response
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($request);
    }

    public function asController(Organisation $organisation, Production $production, ActionRequest $request): Response
    {
        $this->parent = $production;
        $this->initialisationFromProduction($production, $request);

        return $this->handle($request);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            IndexManufactureTasks::make()->getBreadcrumbs(request()->route()->getName(), $routeParameters),
            [
                [
                    'type'         => 'creatingModel',
                    'creatingModel'=> [
                        'label'=> __('creating task'),
                    ]
                ]
            ]
        );
    }
}
