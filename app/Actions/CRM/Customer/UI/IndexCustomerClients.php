<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\CRM\CustomerClientResource;
use App\Http\Resources\CRM\CustomersResource;
use App\InertiaTable\InertiaTable;
use App\Models\CRM\Customer;
use App\Models\Catalogue\Shop;
use App\Models\Dropshipping\CustomerClient;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexCustomerClients extends OrgAction
{
    private Customer $parent;
    // private bool $canCreateShop = false;

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
                $query->whereAnyWordStartWith('customer_clients.name', $value)
                    ->orWhereStartWith('customer_clients.email', $value)
                    ->orWhere('customer_clients.reference', '=', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(CustomerClient::class);


        if (class_basename($parent) == 'Customer') {
            $queryBuilder->where('customer_clients.customer_id', $parent->id);
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
            ->defaultSort('customer_clients.slug')
            ->select([
                'customer_clients.location',
                'customer_clients.reference',
                'customer_clients.id',
                'customer_clients.name',
                'customer_clients.slug',
                'customers.reference as customer_reference',
                'customers.slug as customer_slug',
                'customer_clients.created_at'
            ])
            ->leftJoin('customers', 'customers.id', 'customer_id')
            ->allowedSorts(['reference', 'name', 'slug', 'created_at'])
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
                        'title'       => __("No clients found"),
                        'description' =>  __("You can add your client 🤷🏽‍♂️"),
                        'count'       => $parent->stats->number_clients,
                        'action'      => [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new client'),
                            'label'   => __('client'),
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
                ->column(key: 'slug', label: __('slug'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'location', label: __('location'), canBeHidden: false, searchable: true)
                ->column(key: 'created_at', label: __('since'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $customerClients): AnonymousResourceCollection
    {
        return CustomerClientResource::collection($customerClients);
    }

    public function htmlResponse(LengthAwarePaginator $customerClients, ActionRequest $request): Response
    {
        $scope     = $this->parent;


        return Inertia::render(
            'Org/Shop/CRM/CustomerClients',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('customer clients'),
                'pageHead'    => [
                    'title'     => __('customer clients'),
                    'icon'      => [
                        'icon'  => ['fal', 'fa-user'],
                        'title' => __('customer client')
                    ],
                    'actions' => [
                        [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('New Client'),
                            'label'   => __('New Client'),
                            'route'   => [
                                 'name'       => 'grp.org.shops.show.crm.customers.show.customer-clients.create',
                                'parameters' => [
                                    'organisation' => $scope->organisation->slug,
                                    'shop'         => $scope->shop->slug,
                                    'customer'     => $scope->slug
                                ]
                            ]
                        ],
                    ],
                ],
                'data'        => CustomerClientResource::collection($customerClients),

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
                        'label' => __('Clients'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.crm.customers.show.customer-clients.index' =>
            array_merge(
                ShowCustomer::make()->getBreadcrumbs('grp.org.shops.show.crm.customers.show',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.crm.customers.show.customer-clients.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}