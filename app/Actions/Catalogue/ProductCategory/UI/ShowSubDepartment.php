<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:35:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\Catalogue\WithSubDepartmentSubNavigation;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\Mail\Mailshot\UI\IndexMailshots;
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

class ShowSubDepartment extends OrgAction
{
    use HaCatalogueAuthorisation;
    use WithSubDepartmentSubNavigation;


    private Organisation|Shop|ProductCategory $parent;

    public function handle(ProductCategory $subDepartment): ProductCategory
    {
        return $subDepartment;
    }

    public function inOrganisation(Organisation $organisation, ProductCategory $subDepartment, ActionRequest $request): ProductCategory
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(DepartmentTabsEnum::values());

        return $this->handle($subDepartment);
    }

    public function asController(Organisation $organisation, Shop $shop, ProductCategory $subDepartment, ActionRequest $request): ProductCategory
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(DepartmentTabsEnum::values());

        return $this->handle($subDepartment);
    }

    public function inDepartment(Organisation $organisation, Shop $shop, ProductCategory $department, ProductCategory $subDepartment, ActionRequest $request): ProductCategory
    {
        $this->parent = $department;
        $this->initialisationFromShop($shop, $request)->withTab(DepartmentTabsEnum::values());

        return $this->handle($subDepartment);
    }

    public function htmlResponse(ProductCategory $subDepartment, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Catalogue/Department',
            [
                'title'       => __('sub department'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($subDepartment, $request),
                    'next'     => $this->getNext($subDepartment, $request),
                ],
                'pageHead'    => [
                    'title'     => $subDepartment->name,
                    'model'     => __('Sub Department'),
                    'icon'      => [
                        'icon'  => ['fal', 'fa-folder-tree'],
                        'title' => __('Sub Department')
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
                    'subNavigation' => $this->getSubDepartmentSubNavigation($subDepartment)
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => DepartmentTabsEnum::navigation()
                ],

                DepartmentTabsEnum::SHOWCASE->value => $this->tab == DepartmentTabsEnum::SHOWCASE->value ?
                    fn () => GetProductCategoryShowcase::run($subDepartment)
                    : Inertia::lazy(fn () => GetProductCategoryShowcase::run($subDepartment)),

                DepartmentTabsEnum::CUSTOMERS->value => $this->tab == DepartmentTabsEnum::CUSTOMERS->value
                    ?
                    fn () => CustomersResource::collection(
                        IndexCustomers::run(
                            parent: $subDepartment->shop,
                            prefix: 'customers'
                        )
                    )
                    : Inertia::lazy(fn () => CustomersResource::collection(
                        IndexCustomers::run(
                            parent: $subDepartment->shop,
                            prefix: 'customers'
                        )
                    )),
                DepartmentTabsEnum::MAILSHOTS->value => $this->tab == DepartmentTabsEnum::MAILSHOTS->value
                    ?
                    fn () => MailshotResource::collection(
                        IndexMailshots::run(
                            parent: $subDepartment,
                            prefix: 'mailshots'
                        )
                    )
                    : Inertia::lazy(fn () => MailshotResource::collection(
                        IndexMailshots::run(
                            parent: $subDepartment,
                            prefix: 'mailshots'
                        )
                    )),


                DepartmentTabsEnum::HISTORY->value => $this->tab == DepartmentTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($subDepartment))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($subDepartment))),





            ]
        )->table(
            IndexCustomers::make()->tableStructure(
                parent: $subDepartment->shop,
                prefix: 'customers'
            )
        )->table(
            IndexMailshots::make()->tableStructure(
                parent: $subDepartment->shop,
                prefix: 'mailshots'
            )
        )

            ->table(IndexHistory::make()->tableStructure(prefix: DepartmentTabsEnum::HISTORY->value));
    }


    public function jsonResponse(ProductCategory $subDepartment): DepartmentsResource
    {
        return new DepartmentsResource($subDepartment);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (ProductCategory $subDepartment, array $routeParameters, $suffix) {
            return [

                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Sub departments')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $subDepartment->code,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],

            ];
        };

        $subDepartment=ProductCategory::where('slug', $routeParameters['subDepartment'])->first();

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
                    $subDepartment,
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
            'grp.org.shops.show.catalogue.departments.show.sub-departments.show' =>
            array_merge(
                (new ShowDepartment())->getBreadcrumbs('grp.org.shops.show.catalogue.departments.show', $routeParameters),
                $headCrumb(
                    $subDepartment,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.catalogue.departments.show.sub-departments.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.catalogue.departments.show.sub-departments.show',
                            'parameters' => $routeParameters


                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(ProductCategory $subDepartment, ActionRequest $request): ?array
    {
        $previous = ProductCategory::where('code', '<', $subDepartment->code)->where('shop_id', $this->shop->id)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(ProductCategory $subDepartment, ActionRequest $request): ?array
    {
        $next = ProductCategory::where('code', '>', $subDepartment->code)->where('shop_id', $this->shop->id)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?ProductCategory $subDepartment, string $routeName): ?array
    {
        if (!$subDepartment) {
            return null;
        }

        return match ($routeName) {
            'shops.families.show' => [
                'label' => $subDepartment->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'department' => $subDepartment->slug
                    ]
                ]
            ],
            'shops.show.families.show' => [
                'label' => $subDepartment->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'shop'       => $subDepartment->shop->slug,
                        'department' => $subDepartment->slug
                    ]
                ]
            ],
            default => [] // Add a default case to handle unmatched route names
        };
    }
}
