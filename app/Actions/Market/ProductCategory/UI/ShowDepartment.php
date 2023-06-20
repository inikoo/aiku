<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:35:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\ProductCategory\UI;

use App\Actions\Helpers\History\IndexHistories;
use App\Actions\InertiaAction;
use App\Actions\Mail\Mailshot\IndexMailshots;
use App\Actions\Market\Product\UI\IndexProducts;
use App\Actions\Sales\Customer\UI\IndexCustomers;
use App\Actions\UI\Catalogue\CatalogueHub;
use App\Enums\UI\DepartmentTabsEnum;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Market\DepartmentResource;

use App\Http\Resources\Market\ProductResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\SysAdmin\HistoryResource;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowDepartment extends InertiaAction
{
    public function handle(ProductCategory $department): ProductCategory
    {
        return $department;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.departments.edit');

        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function inTenant(ProductCategory $department, ActionRequest $request): ProductCategory
    {
        $this->initialisation($request)->withTab(DepartmentTabsEnum::values());

        return $this->handle($department);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, ProductCategory $department, ActionRequest $request): ProductCategory
    {
        $this->initialisation($request)->withTab(DepartmentTabsEnum::values());

        return $this->handle($department);
    }

    public function htmlResponse(ProductCategory $department, ActionRequest $request): Response
    {
        //        $this->validateAttributes();

        return Inertia::render(
            'Market/Department',
            [
                'title'                              => __('department'),
                'breadcrumbs'                        => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($department, $request),
                    'next'     => $this->getNext($department, $request),
                ],
                'pageHead'                           => [
                    'title' => $department->name,
                    'icon'  => [
                        'icon'  => ['fal', 'fa-folders'],
                        'title' => __('department')
                    ],
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                ],
                'tabs'                               => [
                    'current'    => $this->tab,
                    'navigation' => DepartmentTabsEnum::navigation()
                ],

                DepartmentTabsEnum::SHOWCASE->value => $this->tab == DepartmentTabsEnum::SHOWCASE->value ?
                    fn () => GetProductCategoryShowcase::run($department)
                    : Inertia::lazy(fn () => GetProductCategoryShowcase::run($department)),

                DepartmentTabsEnum::CUSTOMERS->value => $this->tab == DepartmentTabsEnum::CUSTOMERS->value ?
                    fn () => CustomerResource::collection(IndexCustomers::run($department))
                    : Inertia::lazy(fn () => CustomerResource::collection(IndexCustomers::run($department))),

                DepartmentTabsEnum::MAILSHOTS->value => $this->tab == DepartmentTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotResource::collection(IndexMailshots::run($department))
                    : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($department))),

                DepartmentTabsEnum::HISTORY->value => $this->tab == DepartmentTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistories::run($department))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistories::run($department))),

                /*
                DepartmentTabsEnum::FAMILIES->value  => $this->tab == DepartmentTabsEnum::FAMILIES->value ?
                    fn () => [
                        'table'             => FamilyResource::collection(IndexFamilies::run($this->department)),
                        'createInlineModel' => [
                            'buttonLabel' => __('family'),
                            'dialog'      => [
                                'title'       => __('new family'),
                                'saveLabel'   => __('save'),
                                'cancelLabel' => __('cancel')
                            ]
                        ],
                    ]
                    : Inertia::lazy(
                        fn () => [
                            'table'             => FamilyResource::collection(IndexFamilies::run($this->department)),
                            'createInlineModel' => [
                                'buttonLabel' => __('family'),
                                'dialog'      => [
                                    'title'       => __('new family'),
                                    'saveLabel'   => __('save'),
                                    'cancelLabel' => __('cancel')
                                ]
                            ],
                        ]
                    ),
*/

                DepartmentTabsEnum::PRODUCTS->value  => $this->tab == DepartmentTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(IndexProducts::run($department))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($department))),

            ]
        )->table(IndexCustomers::make()->tableStructure($department))
            ->table(IndexMailshots::make()->tableStructure($department))
//            ->table(IndexFamilies::make()->tableStructure($department))
            ->table(IndexProducts::make()->tableStructure($department))
            ->table(IndexHistories::make()->tableStructure());
    }


    public function jsonResponse(ProductCategory $department): DepartmentResource
    {
        return new DepartmentResource($department);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (ProductCategory $department, array $routeParameters, $suffix) {
            return [

                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('departments')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $department->slug,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],

            ];
        };


        return match ($routeName) {
            'shops.show.departments.show' =>
            array_merge(
                CatalogueHub::make()->getBreadcrumbs('shops', []),
                $headCrumb(
                    $routeParameters['department'],
                    [
                        'index' => [
                            'name'       => 'shops.show.departments.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'shops.show.departments.show',
                            'parameters' => [
                                $routeParameters['department']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            'shops.show.departments.show' =>
            array_merge(
                CatalogueHub::make()->getBreadcrumbs('shops.show.hub', ['shop' => $routeParameters['shop']]),
                $headCrumb(
                    $routeParameters['department'],
                    [
                        'index' => [
                            'name'       => 'shops.show.departments.index',
                            'parameters' => [$routeParameters['shop']->slug]
                        ],
                        'model' => [
                            'name'       => 'shops.show.departments.show',
                            'parameters' => [
                                $routeParameters['shop']->slug,
                                $routeParameters['department']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(ProductCategory $department, ActionRequest $request): ?array
    {
        $previous = ProductCategory::where('code', '<', $department->code)->orderBy('code', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(ProductCategory $department, ActionRequest $request): ?array
    {
        $next = ProductCategory::where('code', '>', $department->code)->orderBy('code')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?ProductCategory $department, string $routeName): ?array
    {
        if(!$department) {
            return null;
        }
        return match ($routeName) {
            'shops.show.departments.show'=> [
                'label'=> $department->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'department'=> $department->slug
                    ]
                ]
            ],
            'shops.show.departments.show'=> [
                'label'=> $department->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'shop'      => $department->shop->slug,
                        'department'=> $department->slug
                    ]
                ]
            ],
        };
    }
}
