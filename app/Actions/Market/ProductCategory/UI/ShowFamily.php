<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:35:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\ProductCategory\UI;

use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\InertiaAction;
use App\Actions\Mail\Mailshot\IndexMailshots;
use App\Actions\Market\Product\UI\IndexProducts;
use App\Actions\Market\Shop\UI\IndexShops;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Enums\UI\DepartmentTabsEnum;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Market\DepartmentsResource;
use App\Http\Resources\Market\ProductResource;
use App\Http\Resources\Sales\CustomersResource;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowFamily extends InertiaAction
{
    public function handle(ProductCategory $family): ProductCategory
    {
        return $family;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo('shops.edit');
        $this->canDelete = $request->user()->hasPermissionTo('shops.edit');

        return $request->user()->hasPermissionTo("shops.families.view");
    }

    public function inOrganisation(ProductCategory $family, ActionRequest $request): ProductCategory
    {
        $this->initialisation($request)->withTab(DepartmentTabsEnum::values());

        return $this->handle($family);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, ProductCategory $family, ActionRequest $request): ProductCategory
    {
        $this->initialisation($request)->withTab(DepartmentTabsEnum::values());

        return $this->handle($family);
    }

    public function htmlResponse(ProductCategory $family, ActionRequest $request): Response
    {

        return Inertia::render(
            'Market/Department',
            [
                'title'                              => __('department'),
                'breadcrumbs'                        => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($family, $request),
                    'next'     => $this->getNext($family, $request),
                ],
                'pageHead'                           => [
                    'title' => $family->name,
                    'icon'  => [
                        'icon'  => ['fal', 'fa-folder'],
                        'title' => __('department')
                    ],
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                        $this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'shops.show.families.remove',
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
                    fn () => GetProductCategoryShowcase::run($family)
                    : Inertia::lazy(fn () => GetProductCategoryShowcase::run($family)),

                DepartmentTabsEnum::CUSTOMERS->value => $this->tab == DepartmentTabsEnum::CUSTOMERS->value ?
                    fn () => CustomersResource::collection(IndexCustomers::run($family))
                    : Inertia::lazy(fn () => CustomersResource::collection(IndexCustomers::run($family))),
                DepartmentTabsEnum::MAILSHOTS->value => $this->tab == DepartmentTabsEnum::MAILSHOTS->value ?
                    fn () => MailshotResource::collection(IndexMailshots::run($family))
                    : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($family))),

                /*
                DepartmentTabsEnum::FAMILIES->value  => $this->tab == DepartmentTabsEnum::FAMILIES->value ?
                    fn () => [
                        'table'             => FamiliesResource::collection(IndexFamilies::run($this->department)),
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
                            'table'             => FamiliesResource::collection(IndexFamilies::run($this->department)),
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
                    fn () => ProductResource::collection(IndexProducts::run($family))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($family))),

            ]
        )->table(IndexCustomers::make()->tableStructure($family))
            ->table(IndexMailshots::make()->tableStructure($family))
            ->table(IndexProducts::make()->tableStructure($family));
    }


    public function jsonResponse(ProductCategory $family): DepartmentsResource
    {
        return new DepartmentsResource($family);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (ProductCategory $family, array $routeParameters, $suffix) {
            return [

                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('families')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $family->slug,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],

            ];
        };

        return match ($routeName) {
            'shops.families.show' =>
            array_merge(
                IndexShops::make()->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['family'],
                    [
                        'index' => [
                            'name'       => 'shops.show.families.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'shops.show.families.show',
                            'parameters' => [
                                $routeParameters['family']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            'shops.show.families.show' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters['shop']),
                $headCrumb(
                    $routeParameters['family'],
                    [
                        'index' => [
                            'name'       => 'shops.show.families.index',
                            'parameters' => [$routeParameters['shop']->slug]
                        ],
                        'model' => [
                            'name'       => 'shops.show.families.show',
                            'parameters' => [
                                $routeParameters['shop']->slug,
                                $routeParameters['family']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(ProductCategory $family, ActionRequest $request): ?array
    {
        $previous = ProductCategory::where('code', '<', $family->code)->orderBy('code', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(ProductCategory $family, ActionRequest $request): ?array
    {
        $next = ProductCategory::where('code', '>', $family->code)->orderBy('code')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?ProductCategory $family, string $routeName): ?array
    {
        if(!$family) {
            return null;
        }
        return match ($routeName) {
            'shops.families.show'=> [
                'label'=> $family->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'department'=> $family->slug
                    ]
                ]
            ],
            'shops.show.families.show'=> [
                'label'=> $family->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'shop'      => $family->shop->slug,
                        'department'=> $family->slug
                    ]
                ]
            ],
        };
    }
}
