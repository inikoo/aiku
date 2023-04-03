<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 22 Feb 2023 12:20:38 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Sales\Order;

use App\Actions\InertiaAction;
use App\Actions\Sales\Order\UI\HasUIOrders;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Sales\OrderResource;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexOrders extends InertiaAction
{
    use HasUIOrders;
    public function handle(Tenant|Shop|Customer $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('orders.number', '~*', "\y$value\y")
                    ->orWhere('orders.date', '=', $value);
            });
        });
        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::ORDERS->value);



        return QueryBuilder::for(Order::class)
            ->defaultSort('orders.number')
            ->select([
                'orders.number',
                'orders.date',
                'orders.state',
                'orders.created_at',
                'orders.updated_at',
                'orders.slug',
                'shops.slug as shop_slug'
            ])
            ->leftJoin('order_stats', 'orders.id', 'order_stats.order_id')
            ->leftJoin('shops', 'orders.shop_id', 'shops.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('orders.shop_id', $parent->id);
                } elseif (class_basename($parent) == 'Customer') {
                    $query->where('orders.customer_id', $parent->id);
                }
            })
            ->allowedSorts(['number', 'date'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::ORDERS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::ORDERS->value)
                ->pageName(TabsAbbreviationEnum::ORDERS->value.'Page');

            $table->column(key: 'number', label: __('number'), canBeHidden: false, sortable: true, searchable: true);


            $table->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.products.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.products.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $orders): AnonymousResourceCollection
    {
        return OrderResource::collection($orders);
    }


    public function htmlResponse(LengthAwarePaginator $orders, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'Marketing/Orders',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $parent
                ),
                'title'       => __('orders'),
                'pageHead'    => [
                    'title'   => __('orders'),
                    'create'  => $this->canEdit && $this->routeName=='shops.show.orders.index' ? [
                        'route' => [
                            'name'       => 'shops.show.orders.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('order')
                    ] : false,
                ],
                'data'        => OrderResource::collection($orders),


            ]
        )->table($this->tableStructure($parent));
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request);

        return $this->handle(parent: app('currentTenant'));
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(parent: $shop);
    }
}
