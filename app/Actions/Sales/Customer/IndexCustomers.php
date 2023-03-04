<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\Sales\InertiaTableCustomerResource;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class IndexCustomers extends InertiaAction
{
    private Shop|Tenant $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {


                $query->where('customers.name', '~*', "\y$value\y")
                    ->orWhere('customers.email', 'LIKE', "%$value")
                    ->orWhere('customers.reference', '=', $value);
            });
        });


        return QueryBuilder::for(Customer::class)
            ->defaultSort('customers.reference')
            ->select(['reference', 'customers.id', 'customers.name','customers.slug', 'shops.code as shop_code', 'shops.slug as shop_slug', 'number_active_clients'])
            ->leftJoin('customer_stats', 'customers.id', 'customer_stats.customer_id')
            ->leftJoin('shops', 'shops.id', 'shop_id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'Shop') {
                    $query->where('customers.shop_id', $this->parent->id);
                }
            })
            ->allowedSorts(['reference', 'name', 'number_active_clients'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.customers.view')
            );
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return CustomerResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $shops)
    {
        return Inertia::render(
            'Sales/Customers',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('customers'),
                'pageHead'    => [
                    'title' => __('customers'),
                ],
                'customers'   => InertiaTableCustomerResource::collection($shops),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->defaultSort('reference');

            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);

            if (class_basename($this->parent) == 'Tenant') {
                $table->column(key: 'shop', label: __('shop'), canBeHidden: false, sortable: true, searchable: true);
            }

            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
            if (class_basename($this->parent) == 'Shop' and $this->parent->subtype == 'dropshipping') {
                $table->column(key: 'number_active_clients', label: __('clients'), canBeHidden: false, sortable: true);
            }
        });
    }


    public function asController(Request $request): LengthAwarePaginator
    {
        $this->fillFromRequest($request);
        $this->parent    = app('currentTenant');
        $this->routeName = $request->route()->getName();

        return $this->handle();
    }

    public function InShop(Shop $shop): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->validateAttributes();

        return $this->handle();
    }

    public function getBreadcrumbs(string $routeName, Shop|Tenant $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('customers')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'customers.index' => $headCrumb(),
            'shops.show.customers.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            default => []
        };
    }

}
