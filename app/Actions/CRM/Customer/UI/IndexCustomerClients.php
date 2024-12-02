<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\UI;

use App\Actions\OrgAction;
use App\Http\Resources\CRM\CustomerClientResource;
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
    use WithCustomerSubNavigation;

    private Customer $parent;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");

        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.view");
    }

    public function asController(Organisation $organisation, Shop $shop, Customer $customer, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $customer;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($customer);
    }

    public function handle(Customer $parent, $prefix = null): LengthAwarePaginator
    {
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
            ->defaultSort('customer_clients.reference')
            ->select([
                'customer_clients.location',
                'customer_clients.reference',
                'customer_clients.id',
                'customer_clients.name',
                'customer_clients.ulid',
                'customers.reference as customer_reference',
                'customers.slug as customer_slug',
                'customer_clients.created_at'
            ])
            ->leftJoin('customers', 'customers.id', 'customer_id')
            ->allowedSorts(['reference', 'name', 'created_at'])
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
                        'Customer' => [
                            'title'       => __("No clients found"),
                            'description' => __("You can add your client 🤷🏽‍♂️"),
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
                )
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
        $scope = $this->parent;

        $subNavigation = $this->getCustomerDropshippingSubNavigation($this->parent, $request);


        $icon       = ['fal', 'fa-user'];
        $title      = $this->parent->name;
        $iconRight  = [
            'icon'  => ['fal', 'fa-user-friends'],
            'title' => __('customer client')
        ];
        $afterTitle = [

            'label' => __('Clients')
        ];


        return Inertia::render(
            'Org/Shop/CRM/CustomerClients',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('customer clients'),
                'pageHead'    => [
                    'title'         => $title,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                    'icon'          => $icon,
                    'subNavigation' => $subNavigation,
                    'actions'       => [
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
                ShowCustomer::make()->getBreadcrumbs(
                    'grp.org.shops.show.crm.customers.show',
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
