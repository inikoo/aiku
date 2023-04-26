<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Catalogue;

use App\Actions\InertiaAction;
use App\Actions\Marketing\ProductCategory\UI\IndexDepartments;
use App\Actions\Marketing\Product\UI\IndexProducts;
use App\Actions\Marketing\Shop\ShowShop;
use App\Actions\UI\Dashboard\Dashboard;
use App\Enums\UI\CatalogueTabsEnum;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Http\Resources\Marketing\FamilyResource;
use App\Http\Resources\Marketing\ProductResource;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CatalogueHub extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("showroom.view");
    }


    public function inTenant(ActionRequest $request): ActionRequest
    {
        $this->initialisation($request)->withTab(CatalogueTabsEnum::values());
        return $request;
    }

    public function inShop(Shop $shop, ActionRequest $request): ActionRequest
    {
        $this->initialisation($request)->withTab(CatalogueTabsEnum::values());
        return $request;
    }



    public function htmlResponse(ActionRequest $request): Response
    {
        $parent = match ($request->route()->getName()) {
            'shops.show.catalogue.hub' => $request->route()->parameters()['shop'],
            default                    => app('currentTenant')
        };

        $title = match (class_basename($parent)) {
            'Shop'  => $parent->code.' '.__('catalogue'),
            default => __('catalogue all stores')
        };


        return Inertia::render(
            'Marketing/CatalogueHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters()
                ),
                'title'       => $title,
                'pageHead'    => [
                    'title' => $title,
                ],

                'tabs'                                => [
                    'current'    => $this->tab,
                    'navigation' => CatalogueTabsEnum::navigation()
                ],
                CatalogueTabsEnum::DEPARTMENTS->value => $this->tab == CatalogueTabsEnum::DEPARTMENTS->value ?
                    fn () => [
                        'table'             => DepartmentResource::collection(IndexDepartments::run($parent)),
                        'createInlineModel' => [
                            'buttonLabel' => __('department'),
                            'dialog'      => [
                                'title'       => __('new department'),
                                'saveLabel'   => __('save'),
                                'cancelLabel' => __('cancel')
                            ]
                        ],
                    ]
                    : Inertia::lazy(
                        fn () => [
                            'table'             => DepartmentResource::collection(IndexDepartments::run($parent)),
                            'createInlineModel' => [
                                'buttonLabel' => __('department'),
                                'dialog'      => [
                                    'title'       => __('new department'),
                                    'saveLabel'   => __('save'),
                                    'cancelLabel' => __('cancel')
                                ]
                            ],
                        ]
                    ),
                CatalogueTabsEnum::FAMILIES->value => $this->tab == CatalogueTabsEnum::FAMILIES->value ?
                    fn () => FamilyResource::collection(IndexFamilies::run($parent))
                    : Inertia::lazy(fn () => FamilyResource::collection(IndexFamilies::run($parent))),

                CatalogueTabsEnum::PRODUCTS->value => $this->tab == CatalogueTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(IndexProducts::run($parent))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($parent))),
            ]
        )->table(IndexDepartments::make()->tableStructure($parent))
            ->table(IndexFamilies::make()->tableStructure($parent))
            ->table(IndexProducts::make()->tableStructure($parent));
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
                (new ShowShop())->getBreadcrumbs($routeParameters['shop']),
                $headCrumb([$routeParameters['shop']->slug])
            ),
            default => []
        };
    }
}
