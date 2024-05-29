<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Manufacturing\RawMaterial\UI;

use App\Actions\OrgAction;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStateEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStockStatusEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialUnitEnum;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateRawMaterial extends OrgAction
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
                'title'       => __('new raw material'),
                'pageHead'    => [
                    'title'        => __('new raw material'),
                    'icon'         => [
                        'title' => __('Create raw material'),
                        'icon'  => 'fal fa-industry'
                    ],
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'grp.org.productions.show.crafts.raw_materials.index',
                                'parameters' => $request->route()->originalParameters()
                            ],
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('Create raw material'),
                            'fields' => [

                                'type' => [
                                    'type'     => 'select',
                                    'options'  => RawMaterialTypeEnum::values(),
                                    'label'    => __('type'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'state' => [
                                    'type'     => 'select',
                                    'options'  => RawMaterialStateEnum::values(),
                                    'label'    => __('state'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'code' => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'description' => [
                                    'type'     => 'input',
                                    'label'    => __('description'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'unit' => [
                                    'type'      => 'select',
                                    'options'   => RawMaterialUnitEnum::values(),
                                    'label'     => __('unit'),
                                    'value'     => '',
                                    'required'  => true
                                ],
                                'unit_cost' => [
                                    'type'     => 'input',
                                    'label'    => __('unit cost'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'quantity_on_location' => [
                                    'type'     => 'input',
                                    'label'    => __('quantity on location'),
                                    'value'    => '',
                                    'required' => true
                                ],
                                'stock_status' => [
                                    'type'      => 'select',
                                    'options'   => RawMaterialStockStatusEnum::values(),
                                    'label'     => __('stock status'),
                                    'value'     => '',
                                    'required'  => true
                                ],

                            ]
                        ]
                    ],
                    'route'      => [
                        'name'        => 'grp.models.production.raw-materials.store',
                        'parameters'  => [$this->parent->id]
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
            IndexRawMaterials::make()->getBreadcrumbs(request()->route()->getName(), $routeParameters),
            [
                [
                    'type'         => 'creatingModel',
                    'creatingModel'=> [
                        'label'=> __('Creating raw material'),
                    ]
                ]
            ]
        );
    }
}
