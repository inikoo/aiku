<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Catalogue;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Product\UI\IndexProducts;
use App\Actions\Marketing\ProductCategory\UI\IndexDepartments;
use App\Actions\Marketing\Shop\UI\ShowShop;
use App\Actions\UI\Dashboard\Dashboard;
use App\Enums\UI\CatalogueTabsEnum;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Http\Resources\Marketing\FamilyResource;
use App\Http\Resources\Marketing\ProductResource;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CatalogueHub extends InertiaAction
{
    public function handle(Tenant|Shop $scope): Tenant|Shop
    {
        return $scope;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("showroom.view");
    }


    public function inTenant(ActionRequest $request): Tenant
    {
        $this->initialisation($request)->withTab(CatalogueTabsEnum::values());

        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop, ActionRequest $request): Shop
    {
        $this->initialisation($request)->withTab(CatalogueTabsEnum::values());

        return $this->handle($shop);
    }


    public function htmlResponse(Tenant|Shop $scope, ActionRequest $request): Response
    {
        $container = null;
        $scopeType = 'Tenant';
        $title     = __('catalogue all stores');
        if (class_basename($scope) == 'Shop') {
            $title     = __('catalogue');
            $scopeType = 'Shop';
            $container = [
                'icon'    => ['fal', 'fa-store-alt'],
                'tooltip' => __('Shop'),
                'label'   => Str::possessive($scope->name)
            ];
        }


        return Inertia::render(
            'Marketing/CatalogueHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters()
                ),
                'title'       => $title,
                'pageHead'    => [
                    'title'     => $title,
                    'container' => $container,

                ],

                'tabs'                                => [
                    'current'    => $this->tab,
                    'navigation' => CatalogueTabsEnum::navigation()
                ],
                CatalogueTabsEnum::DEPARTMENTS->value => $this->tab == CatalogueTabsEnum::DEPARTMENTS->value
                    ?
                    fn () =>
                        DepartmentResource::collection(IndexDepartments::run($scope))


                    : Inertia::lazy(
                        fn () =>
                            DepartmentResource::collection(IndexDepartments::run($scope))
                    ),
                /*
                 CatalogueTabsEnum::FAMILIES->value => $this->tab == CatalogueTabsEnum::FAMILIES->value ?
                     fn () => FamilyResource::collection(IndexFamilies::run($scope))
                     : Inertia::lazy(fn () => FamilyResource::collection(IndexFamilies::run($scope))),
 */
                CatalogueTabsEnum::PRODUCTS->value    => $this->tab == CatalogueTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(IndexProducts::run($scope))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($scope))),
            ]
        )->table(IndexDepartments::make()->tableStructure($scope))
            //  ->table(IndexFamilies::make()->tableStructure($scope))
            ->table(IndexProducts::make()->tableStructure($scope));
    }


    public function getBreadcrumbs($routeName, $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => $routeName,
                            'parameters' => $routeParameters
                        ],
                        'label' => __('catalogue'),
                        'icon'  => 'fal fa-folder-tree'
                    ],
                ],
            ];
        };


        return match ($routeName) {
            'catalogue.hub' => array_merge(
                Dashboard::make()->getBreadcrumbs(),
                $headCrumb()
            ),
            'shops.show.catalogue.hub' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($routeParameters),
                $headCrumb([$routeParameters['shop']->slug])
            ),
            default => []
        };
    }
}
