<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 21:14:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\InertiaAction;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
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

class IndexFulfilmentCustomers extends InertiaAction
{
    public function handle(Tenant|Shop $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('customers.name', '~*', "\y$value\y")
                    ->orWhere('customers.email', 'ILIKE', "%$value")
                    ->orWhere('customers.reference', '=', $value);
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::CUSTOMERS->value);

        return QueryBuilder::for(Customer::class)
            ->defaultSort('customers.reference')
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
            ->allowedSorts(['name', 'number_active_clients'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::CUSTOMERS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'slug', label: __('slug'), canBeHidden: false, sortable: true, searchable: true);

            if (class_basename($parent) == 'Tenant') {
                $table->column(key: 'shop', label: __('shop'), canBeHidden: false, sortable: true, searchable: true);
            }

            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
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
            'Fulfilment/Customers',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('customers'),
                'pageHead'    => [
                    'title'  => __('customers'),
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
            'customers.index'            =>
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'=> 'customers.index',
                        null
                    ]
                ),
            ),


            'shops.show.customers.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'      => 'shops.show.customers.index',
                        'parameters'=>
                        [
                            $routeParameters['shop']->slug
                        ]
                    ]
                )
            ),
            default => []
        };
    }
}
