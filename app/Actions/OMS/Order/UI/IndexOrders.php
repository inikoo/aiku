<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order\UI;

use App\Actions\InertiaAction;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\UI\CRM\CRMDashboard;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Sales\OrderResource;
use App\InertiaTable\InertiaTable;
use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use App\Models\OMS\Order;
use App\Models\Organisation\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexOrders extends InertiaAction
{
    public function handle(Organisation|Shop|Customer $parent): LengthAwarePaginator
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
                ->pageName(TabsAbbreviationEnum::ORDERS->value.'Page')
                ->withEmptyState(
                    [
                        'title' => __("No orders found"),
                        'count' => $parent->orders->count()
                    ]
                );

            $table->column(key: 'number', label: __('number'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('shops.products.edit');

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
            'OMS/Orders',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
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


    public function inTenant(ActionRequest $request): LengthAwarePaginator
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

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('orders'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.crm.orders.index' =>
            array_merge(
                (new CRMDashboard())->getBreadcrumbs(
                    'grp.crm.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name' => 'grp.crm.orders.index',
                        null
                    ]
                ),
            ),
            'grp.crm.shops.show.orders.index' =>
            array_merge(
                (new CRMDashboard())->getBreadcrumbs(
                    'grp.crm.shops.show.dashboard',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.crm.shops.show.orders.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            'orders.index'            =>

            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'=> 'orders.index',
                        null
                    ]
                ),
            ),

            'shops.show.orders.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'      => 'shops.show.orders.index',
                        'parameters'=>
                            [
                                $routeParameters['shop']
                            ]
                    ]
                )
            ),
            default => []
        };
    }
}
