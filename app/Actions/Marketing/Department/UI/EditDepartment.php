<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Department\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Models\Marketing\Department;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

class EditDepartment extends InertiaAction
{
    use HasUIDepartment;
    public function handle(Department $department): Department
    {
        return $department;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.products.edit");
    }

    public function asController(Department $department, ActionRequest $request): Department
    {
        $this->initialisation($request);

        return $this->handle($department);
    }

    public function inShop(Shop $shop, Department $department, ActionRequest $request): Department
    {
        $this->initialisation($request);

        return $this->handle($department);
    }

    public function htmlResponse(Department $department): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('department'),
                'breadcrumbs' => $this->getBreadcrumbs($department),
                'pageHead'    => [
                    'title'     => $department->code,
                    'exitEdit'  => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ],


                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [
                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $department->code
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('label'),
                                    'value' => $department->name
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'models.department.update',
                            'parameters'=> $department->slug

                        ],
                    ]
                ]
            ]
        );
    }

    #[Pure] public function jsonResponse(Department $department): DepartmentResource
    {
        return new DepartmentResource($department);
    }
}
