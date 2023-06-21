<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\UI;

use App\Actions\InertiaAction;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\UI\CRM\CRMDashboard;
use App\Actions\UI\Dashboard\Dashboard;
use App\Http\Resources\Sales\CustomerResource;
use App\InertiaTable\InertiaTable;
use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use App\Models\Tenancy\Tenant;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexCustomers extends InertiaAction
{
    public function handle(Tenant|Shop $parent, $prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('customers.name', '~*', "\y$value\y")
                    ->orWhere('customers.email', 'ILIKE', "%$value")
                    ->orWhere('customers.reference', '=', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return QueryBuilder::for(Customer::class)
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
            ->when(true, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('customers.shop_id', $parent->id);
                }
            })
            ->allowedSorts(['reference', 'name', 'number_active_clients','slug'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null, $prefix=null): Closure
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
                ->column(key: 'slug', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
            if (class_basename($parent) == 'Shop' and $parent->subtype == 'dropshipping') {
                $table->column(key: 'number_active_clients', label: __('clients'), canBeHidden: false, sortable: true);
            }
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('crm.customers.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('crm.customers.view')
            );
    }


    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle($shop);
    }


    public function jsonResponse(LengthAwarePaginator $customers): AnonymousResourceCollection
    {
        return CustomerResource::collection($customers);
    }


    public function htmlResponse(LengthAwarePaginator $customers, ActionRequest $request): Response
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'CRM/Customers',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('customers'),
                'pageHead'    => [
                    'title'  => __('customers'),
                    'create' => $this->canEdit
                    && (
                        $this->routeName == 'shops.show.customers.index'
                    )

                        ? [
                            'route' =>
                                match ($this->routeName) {
                                    'shops.show.customers.index' =>
                                    [
                                        'name'       => 'shops.show.customers.create',
                                        'parameters' => array_values($this->originalParameters)
                                    ],
                                    'customers.index' =>
                                    [
                                        'name'       => 'customers.create',
                                        'parameters' => array_values($this->originalParameters)
                                    ]
                                }


                            ,
                            'label' => __('customers')
                        ] : false,

                ],
                'data'        => CustomerResource::collection($customers),


            ]
        )->table($this->tableStructure($parent));
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
            'customers.index' =>
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'customers.index',
                        null
                    ]
                ),
            ),
            'crm.customers.index' =>
            array_merge(
                (new CRMDashboard())->getBreadcrumbs(
                    'crm.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name' => 'crm.customers.index',
                        null
                    ]
                ),
            ),
            'crm.shops.show.customers.index' =>
            array_merge(
                (new CRMDashboard())->getBreadcrumbs(
                    'crm.shops.show.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'crm.shops.show.customers.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),

            'shops.show.customers.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => 'shops.show.customers.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
