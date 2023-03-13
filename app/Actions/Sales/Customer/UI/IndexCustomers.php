<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 21:14:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\Sales\InertiaTableCustomerResource;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexCustomers extends InertiaAction
{
    use HasUICustomers;
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
        $this->can_edit = $request->user()->can('shops.customers.edit');
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
            'Sales/CreateCustomer',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('customers'),
                'pageHead'    => [
                    'title' => __('customers'),
                    'create'  => $this->can_edit && $this->routeName=='shops.show.customers.index' ? [
                        'route' => [
                            'name'       => 'shops.show.customers.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=>__('customer')
                    ] : false,

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


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent    = app('currentTenant');
        $this->initialisation($request);

        return $this->handle();
    }

    public function inShop(Shop $shop,ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisation($request);

        return $this->handle();
    }


}
