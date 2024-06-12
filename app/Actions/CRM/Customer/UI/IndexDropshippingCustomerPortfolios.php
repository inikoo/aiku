<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\UI;

use App\Actions\OrgAction;
use App\Http\Resources\CRM\CustomerClientResource;
use App\Http\Resources\CRM\DropshippingCustomerPortfolioResource;
use App\InertiaTable\InertiaTable;
use App\Models\CRM\Customer;
use App\Models\Catalogue\Shop;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Dropshipping\DropshippingCustomerPortfolio;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexDropshippingCustomerPortfolios extends OrgAction
{
    private Customer $parent;
    // private bool $canCreateShop = false;
    use WithCustomerSubNavigation;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit       = $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
        $this->canCreateShop = $request->user()->hasPermissionTo('shops.edit');

        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.view");
    }

    public function inCustomer(Organisation $organisation, Shop $shop, Customer $customer, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromShop($shop, $request);
        $this->parent = $customer;

        return $this->handle($customer);
    }


    public function handle(Customer $parent, $prefix = null): LengthAwarePaginator
    {
        // dd($parent->type);
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('dropshipping_customer_portfolios.reference', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(DropshippingCustomerPortfolio::class);


        if (class_basename($parent) == 'Customer') {
            $queryBuilder->where('dropshipping_customer_portfolios.customer_id', $parent->id);
        }


        /*
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }
        */


        return $queryBuilder
            ->defaultSort('dropshipping_customer_portfolios.reference')
            ->select([
                'dropshipping_customer_portfolios.reference',
                'dropshipping_customer_portfolios.status',
                'dropshipping_customer_portfolios.id',
                'products.code as product_code',
                'products.name as product_name',
                'products.slug as slug',
                'dropshipping_customer_portfolios.created_at'
            ])
            ->leftJoin('products', 'products.id', 'product_id')
            ->allowedSorts(['reference', 'created_at'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {

            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->withEmptyState(
                    match (class_basename($parent)) {
                        // 'Organisation' => [
                        //     'title'       => __("No customers found"),
                        //     'description' => $this->canCreateShop && $parent->catalogueStats->number_shops == 0 ? __('Get started by creating a shop. ✨')
                        //         : __("In fact, is no even a shop yet 🤷🏽‍♂️"),
                        //     'count'       => $parent->crmStats->number_customers,
                        //     'action'      => $this->canCreateShop && $parent->catalogueStats->number_shops == 0
                        //         ? [
                        //             'type'    => 'button',
                        //             'style'   => 'create',
                        //             'tooltip' => __('new shop'),
                        //             'label'   => __('shop'),
                        //             'route'   => [
                        //                 'name' => 'shops.create',
                        //             ]
                        //         ]
                        //         :
                        //         [
                        //             'type'    => 'button',
                        //             'style'   => 'create',
                        //             'tooltip' => __('new customer'),
                        //             'label'   => __('customer'),
                        //             'route'   => [
                        //                 'name' => 'shops.create',
                        //             ]
                        //         ]


                        // ],
                        'Customer' => [
                        'title'       => __("No portfolios found"),
                        'description' => __("You can add your portfolio 🤷🏽‍♂️"),
                        'count'       => $parent->stats->number_clients,
                        'action'      => [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new portfolio'),
                            'label'   => __('portfolio'),
                            'route'   => [
                                'name'       => 'grp.org.shops.show.crm.customers.show.customer-clients.create',
                                'parameters' => [
                                    'organisation' => $parent->organisation->slug,
                                    'shop'         => $parent->shop->slug,
                                    'customer'     => $parent->slug
                                ]
                            ]
                        ]
                    ],
                        default => null
                    }
                    /*
                    [
                        'title'       => __('no customers'),
                        'description' => $this->canEdit ? __('Get started by creating a new customer.') : null,
                        'count'       => $this->organisation->stats->number_employees,
                        'action'      => $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new customer'),
                            'label'   => __('customer'),
                            'route'   => [
                                'name'       => 'grp.org.shops.show.crm.customers.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : null
                    ]
                    */
                )
                ->column(key: 'product_code', label: __('product'), canBeHidden: false, searchable: true)
                ->column(key: 'product_name', label: __('product name'), canBeHidden: false, searchable: true)
                ->column(key: 'reference', label: __('customer reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'created_at', label: __('created at'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'action', label: __('action'), canBeHidden: false, sortable: false, searchable: false);
        };
    }

    public function jsonResponse(LengthAwarePaginator $portfolio): AnonymousResourceCollection
    {
        return DropshippingCustomerPortfolioResource::collection($portfolio);
    }

    public function htmlResponse(LengthAwarePaginator $portfolio, ActionRequest $request): Response
    {
        $scope     = $this->parent;
        $subNavigation = null;
        if ($this->parent instanceof Customer) {
            if ($this->parent->is_dropshipping == true) {
                $subNavigation = $this->getCustomerSubNavigation($this->parent);
            }
        }

        return Inertia::render(
            'Org/Shop/CRM/DropshippingCustomerPortfolios',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('portfolios'),
                'pageHead'    => [
                    'title'     => __('portfolios'),
                    'icon'      => [
                        'icon'  => ['fal', 'fa-user'],
                        'title' => __('portfolios')
                    ],
                    'actions' => [
                        [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('Add Product'),
                            'label'   => __('New Item'),
                            'route'   => [
                                 'name'       => 'grp.org.shops.show.crm.customers.show.customer-clients.create',
                                'parameters'  => [
                                    'organisation' => $scope->organisation->slug,
                                    'shop'         => $scope->shop->slug,
                                    'customer'     => $scope->slug
                                ]
                            ]
                        ],
                    ],
                    'subNavigation' => $subNavigation,
                ],
                'data'        => DropshippingCustomerPortfolioResource::collection($portfolio),

            ]
        )->table($this->tableStructure($this->parent));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Portfolios'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.crm.customers.show.portfolios.index' =>
            array_merge(
                ShowCustomer::make()->getBreadcrumbs(
                    'grp.org.shops.show.crm.customers.show',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.crm.customers.show.portfolios.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
