<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:35:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\Catalogue\WithDepartmentSubNavigation;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\Mail\Mailshot\IndexMailshots;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HaCatalogueAuthorisation;
use App\Enums\UI\Catalogue\DepartmentTabsEnum;
use App\Http\Resources\Catalogue\DepartmentsResource;
use App\Http\Resources\CRM\CustomersResource;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Mail\MailshotResource;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowDepartment extends OrgAction
{
    use HaCatalogueAuthorisation;
    use WithDepartmentSubNavigation;


    private Organisation|Shop $parent;

    public function handle(ProductCategory $department): ProductCategory
    {
        return $department;
    }

    public function inOrganisation(Organisation $organisation, ProductCategory $department, ActionRequest $request): ProductCategory
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(DepartmentTabsEnum::values());

        return $this->handle($department);
    }

    public function asController(Organisation $organisation, Shop $shop, ProductCategory $department, ActionRequest $request): ProductCategory
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(DepartmentTabsEnum::values());

        return $this->handle($department);
    }

    public function htmlResponse(ProductCategory $department, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Catalogue/Department',
            [
                'title'       => __('department'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($department, $request),
                    'next'     => $this->getNext($department, $request),
                ],
                'pageHead'    => [
                    'title'     => $department->name,
                    'model'     => __('department'),
                    'icon'      => [
                        'icon'  => ['fal', 'fa-folder-tree'],
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
                                'name'       => 'shops.show.departments.remove',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false
                    ],
                    'subNavigation' => $this->getDepartmentSubNavigation($department)
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => DepartmentTabsEnum::navigation()
                ],

                DepartmentTabsEnum::SHOWCASE->value => $this->tab == DepartmentTabsEnum::SHOWCASE->value ?
                    fn () => GetProductCategoryShowcase::run($department)
                    : Inertia::lazy(fn () => GetProductCategoryShowcase::run($department)),

                DepartmentTabsEnum::CUSTOMERS->value => $this->tab == DepartmentTabsEnum::CUSTOMERS->value
                    ?
                    fn () => CustomersResource::collection(
                        IndexCustomers::run(
                            parent: $department->shop,
                            prefix: 'customers'
                        )
                    )
                    : Inertia::lazy(fn () => CustomersResource::collection(
                        IndexCustomers::run(
                            parent: $department->shop,
                            prefix: 'customers'
                        )
                    )),
                DepartmentTabsEnum::MAILSHOTS->value => $this->tab == DepartmentTabsEnum::MAILSHOTS->value
                    ?
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


                DepartmentTabsEnum::HISTORY->value => $this->tab == DepartmentTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($department))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($department))),





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

            ->table(IndexHistory::make()->tableStructure(prefix: DepartmentTabsEnum::HISTORY->value));
    }


    public function jsonResponse(ProductCategory $department): DepartmentsResource
    {
        return new DepartmentsResource($department);
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
                            'label' => __('Departments')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $department->code,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],

            ];
        };

        $department=ProductCategory::where('slug', $routeParameters['department'])->first();

        return match ($routeName) {
            /*
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
            */
            'grp.org.shops.show.catalogue.departments.show' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $department,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.catalogue.departments.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.catalogue.departments.show',
                            'parameters' => $routeParameters
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
        $previous = ProductCategory::where('code', '<', $department->code)->where('shop_id', $this->shop->id)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(ProductCategory $department, ActionRequest $request): ?array
    {
        $next = ProductCategory::where('code', '>', $department->code)->where('shop_id', $this->shop->id)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?ProductCategory $department, string $routeName): ?array
    {
        if (!$department) {
            return null;
        }

        return match ($routeName) {
            /*
            'shops.departments.show' => [
                'label' => $department->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'department' => $department->slug
                    ]
                ]
            ],
            */
            'grp.org.shops.show.catalogue.departments.show' => [
                'label' => $department->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $department->organisation->slug,
                        'shop'         => $department->shop->slug,
                        'department'   => $department->slug
                    ]
                ]
            ],
        };
    }
}
