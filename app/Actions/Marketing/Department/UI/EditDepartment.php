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
        return $request->user()->hasPermissionTo("shops.departments.edit");
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
            'Marketing/Department',
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
                'department'  => new DepartmentResource($department),
                'treeMaps'    => [
                    [
                        [
                            'name'  => __('departments'),
                            'icon'  => ['fal', 'fa-cube'],
                            'href'  => ['shops.show.departments.index', $department->slug],
                            'index' => [
                                'number' => $department->stats->number_sub_departments
                            ]
                        ],
                    ],
                ]
            ]
        );
    }

    #[Pure] public function jsonResponse(Department $department): DepartmentResource
    {
        return new DepartmentResource($department);
    }
}
