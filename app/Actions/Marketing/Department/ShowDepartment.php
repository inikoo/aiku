<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:04:31 Central European Summer, Benalmádena, Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Department;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\IndexShops;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Models\Marketing\Department;
use App\Models\Marketing\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDepartment extends InertiaAction
{
    use AsAction;
    use WithInertia;

    public function handle(Department $department): Department
    {
        return $department;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function asController(Department $department): Department
    {
        return $this->handle($department);
    }

    public function inShop(Shop $shop, Department $department, Request $request): Department
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

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


                ],
                'department' => new DepartmentResource($department),
                'treeMaps'   => [
                    [
                        [
                            'name'  => __('families'),
                            'icon'  => ['fal', 'fa-folder'],
                            'href'  => ['shops.show.departments.show.families.index', [$department->shop->slug, $department->slug]],
                            'index' => [
                                'number' => $department->stats->number_families
                            ]
                        ],
                        [
                            'name'  => __('products'),
                            'icon'  => ['fal', 'fa-cube'],
                            'href'  => ['shops.show.departments.show.products.index', [$department->shop->slug, $department->slug]],
                            'index' => [
                                'number' => $department->stats->number_products
                            ]
                        ],
                    ],
                ]
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


    public function getBreadcrumbs(Department $department): array
    {
        return array_merge(
            (new IndexShops())->getBreadcrumbs(),
            [
                'shops.show' => [
                    'route'           => 'shops.show',
                    'routeParameters' => $department->id,
                    'name'            => $department->code,
                    'index'           => [
                        'route'   => 'shops.index',
                        'overlay' => __('Departments list')
                    ],
                    'modelLabel' => [
                        'label' => __('department')
                    ],
                ],
            ]
        );
    }
}
