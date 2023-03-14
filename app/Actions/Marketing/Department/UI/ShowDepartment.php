<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:33:00 Central European Standard Time, Malaga, Spain
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

class ShowDepartment extends InertiaAction
{
    use HasUIDepartment;
    public function handle(Department $department): Department
    {
        return $department;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.departments.edit');

        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function asController(Department $department, ActionRequest $request): Department
    {
        $this->initialisation($request);
        return $this->handle($department);
    }

    public function inShop(Shop $shop, Department $department, ActionRequest $request): Department
    {
        //$this->routeName = $request->route()->getName();
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle($department);
    }

    public function htmlResponse(Department $department): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Marketing/Department',
            [
                'title'       => __('department'),
                'breadcrumbs' => $this->getBreadcrumbs($department),
                'pageHead'    => [
                    'title' => $department->name,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                ],
                'department' => new DepartmentResource($department),
            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(Department $department): DepartmentResource
    {
        return new DepartmentResource($department);
    }
}
