<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jan 2024 11:41:27 Malaysia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment\UI\CRM;

use App\Actions\OrgAction;
use App\Actions\UI\CRM\ShowShopCRMDashboard;
use App\Http\Resources\Sales\CustomerResource;
use App\InertiaTable\InertiaTable;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexCustomers extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit       = $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.view");
    }



    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($fulfilment);
    }


    public function handle(Fulfilment $fulfilment, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('customers.name', $value)
                    ->orWhereStartWith('customers.email', $value)
                    ->orWhere('customers.reference', '=', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Customer::class);
        $queryBuilder->where('customers.shop_id', $fulfilment->shop->id);



        /** @noinspection PhpUndefinedMethodInspection */
        return $queryBuilder
            ->defaultSort('customers.slug')
            ->select([
                'reference',
                'customers.id',
                'customers.name',
                'customers.slug',
                'shops.code as shop_code',
                'shops.slug as shop_slug',
                'number_active_clients'
            ])
            ->leftJoin('customer_stats', 'customers.id', 'customer_stats.customer_id')
            ->leftJoin('shops', 'shops.id', 'shop_id')
            ->allowedSorts(['reference', 'name', 'number_active_clients', 'slug'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Fulfilment $fulfilment, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($fulfilment, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }


            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                            'title'       => __("No customers found"),
                            'description' => __("You can add your customer 🤷🏽‍♂️"),
                            'count'       => $fulfilment->shop->crmStats->number_customers,
                            'action'      => [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new customer'),
                                'label'   => __('customer'),
                                'route'   => [
                                    'name'       => 'grp.org.fulfilment.show.customers.create',
                                    'parameters' => [$fulfilment->organisation->slug,$fulfilment->slug]
                                ]
                            ]
                        ]
                )
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);

        };
    }

    public function jsonResponse(LengthAwarePaginator $customers): AnonymousResourceCollection
    {
        return CustomerResource::collection($customers);
    }

    public function htmlResponse(LengthAwarePaginator $customers, ActionRequest $request): Response
    {



        $container = [
            'icon'    => ['fal', 'fa-pallets'],
            'tooltip' => __('Fulfilment Shop'),
            'label'   => Str::possessive($this->fulfilment->shop->name)

        ];

        return Inertia::render(
            'Org/Fulfilment/CRM/Customers',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('customers'),
                'pageHead'    => [
                    'title'     => __('customers'),
                    'container' => $container,
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-user'],
                        'title' => __('customer')
                    ]
                ],
                'data'        => CustomerResource::collection($customers),

            ]
        )->table($this->tableStructure($this->fulfilment));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('customers'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            /*
            'grp.crm.customers.index' =>
            array_merge(
                (new ShowShopCRMDashboard())->getBreadcrumbs(
                    'grp.crm.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name' => 'grp.crm.customers.index',
                        null
                    ]
                ),
            ),
            */
            'grp.crm.shops.show.customers.index' =>
            array_merge(
                (new ShowShopCRMDashboard())->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.crm.shops.show.customers.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
