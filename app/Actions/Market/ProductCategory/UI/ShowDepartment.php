<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:35:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\ProductCategory\UI;

use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\Helpers\History\IndexHistories;
use App\Actions\InertiaAction;
use App\Actions\Mail\Mailshot\IndexMailshots;
use App\Actions\Market\Product\UI\IndexProducts;
use App\Actions\Market\Shop\UI\IndexShops;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Enums\UI\DepartmentTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Market\DepartmentResource;
use App\Http\Resources\Market\FamilyResource;
use App\Http\Resources\Market\ProductResource;
use App\Http\Resources\Sales\CustomerResource;
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
        $this->canEdit   = $request->user()->can('shops.edit');
        $this->canDelete = $request->user()->can('shops.edit');
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
                        'icon'  => ['fal', 'fa-folder-tree'],
                        'title' => __('department')
                    ],
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                        $this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'shops.show.departments.remove',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false
                    ]
                ],
                'tabs'                               => [
                    'current'    => $this->tab,
                    'navigation' => DepartmentTabsEnum::navigation()
                ],

                DepartmentTabsEnum::SHOWCASE->value => $this->tab == DepartmentTabsEnum::SHOWCASE->value ?
                    fn () => GetProductCategoryShowcase::run($department)
                    : Inertia::lazy(fn () => GetProductCategoryShowcase::run($department)),

                DepartmentTabsEnum::CUSTOMERS->value => $this->tab == DepartmentTabsEnum::CUSTOMERS->value ?
                    fn () => CustomerResource::collection(
                        IndexCustomers::run(
                            parent: $department->shop,
                            prefix: 'customers'
                        )
                    )
                    : Inertia::lazy(fn () => CustomerResource::collection(
                        IndexCustomers::run(
                            parent: $department->shop,
                            prefix: 'customers'
                        )
                    )),
                DepartmentTabsEnum::MAILSHOTS->value => $this->tab == DepartmentTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotResource::collection(
                        IndexMailshots::run(
                            parent: $department,
                            prefix: 'mailshots'
                        )
                    )
                    : Inertia::lazy(fn () => MailshotResource::collection(
                        IndexMailshots::run(
                            parent: $department,
                            prefix: 'mailshots'
                        )
                    )),
                DepartmentTabsEnum::PRODUCTS->value  => $this->tab == DepartmentTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(
                        IndexProducts::run(
                            parent: $department,
                            prefix: 'products'
                        )
                    )
                    : Inertia::lazy(fn () => ProductResource::collection(
                        IndexProducts::run(
                            parent: $department,
                            prefix: 'products'
                        )
                    )),

                DepartmentTabsEnum::HISTORY->value => $this->tab == DepartmentTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistories::run($department))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistories::run($department))),


                DepartmentTabsEnum::FAMILIES->value  => $this->tab == DepartmentTabsEnum::FAMILIES->value ?
                    fn () => [
                        'table'             => FamilyResource::collection(
                            IndexFamilies::run(
                                parent: $department->shop,
                                prefix: 'product_categories'
                            )
                        ),
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
                            'table'             => FamilyResource::collection(
                                IndexFamilies::run(
                                    parent: $department->shop,
                                    prefix: 'product_categories'
                                )
                            ),
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



            ]
        )->table(
            IndexCustomers::make()->tableStructure(
                parent: $department->shop,
                prefix: 'customers'
            )
        )->table(
            IndexMailshots::make()->tableStructure(
                parent: $department->shop,
                prefix: 'mailshots'
            )
        )
//            ->table(IndexFamilies::make()->tableStructure($department))
            ->table(
                IndexProducts::make()->tableStructure(
                    parent: $department->shop,
                    modelOperations: [
                        'createLink' => $this->canEdit ? [
                            'route' => [
                                'name'       => 'shops.departments.show.products.create',
                                'parameters' => array_values([$department->shop->slug])
                            ],
                            'label' => __('mailshot')
                        ] : false
                    ],
                    prefix: 'products'
                )
            )
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
            'shops.departments.show' =>
            array_merge(
                IndexShops::make()->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['department'],
                    [
                        'index' => [
                            'name'       => 'shops.departments.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'shops.departments.show',
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
                ShowShop::make()->getBreadcrumbs($routeParameters),
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
            'shops.departments.show'=> [
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
