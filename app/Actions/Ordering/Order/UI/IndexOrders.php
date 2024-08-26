<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Enums\UI\Ordering\OrdersTabsEnum;
use App\Http\Resources\Ordering\OrdersResource;
use App\Http\Resources\Sales\OrderResource;
use App\InertiaTable\InertiaTable;
use App\Models\CRM\Customer;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\Order;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexOrders extends OrgAction
{
    private Organisation|Shop|Customer|Asset $parent;

    public function handle(Organisation|Shop|Customer|Asset $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('orders.reference', '~*', "\y$value\y")
                    ->orWhere('orders.date', '=', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query=QueryBuilder::for(Order::class);

        if (class_basename($parent) == 'Shop') {
            $query->where('orders.shop_id', $parent->id);
        } elseif (class_basename($parent) == 'Customer') {
            $query->where('orders.customer_id', $parent->id);
        }


        return $query->defaultSort('orders.reference')
            ->select([
                'orders.reference',
                'orders.date',
                'orders.state',
                'orders.created_at',
                'orders.updated_at',
                'orders.slug',
                'shops.slug as shop_slug'
            ])
            ->leftJoin('order_stats', 'orders.id', 'order_stats.order_id')
            ->leftJoin('shops', 'orders.shop_id', 'shops.id')
            ->allowedSorts(['reference', 'date'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|Shop|Customer|Asset $parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }


            $table
                ->withEmptyState(
                    [
                        'title' => __("No orders found"),
                        'count' => $parent->salesStats?->number_orders
                    ]
                );

            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("orders.{$this->shop->id}.view");

        return $request->user()->hasPermissionTo("orders.{$this->shop->id}.view");
    }


    public function jsonResponse(LengthAwarePaginator $orders): AnonymousResourceCollection
    {
        return OrderResource::collection($orders);
    }


    public function htmlResponse(LengthAwarePaginator $orders, ActionRequest $request): Response
    {
        return Inertia::render(
            'Ordering/Orders',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('orders'),
                'pageHead'    => [
                    'title' => __('orders'),
                ],
                'data'        => OrderResource::collection($orders),
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => OrdersTabsEnum::navigation(),
                ],

                OrdersTabsEnum::ORDERS->value => $this->tab == OrdersTabsEnum::ORDERS->value ?
                    fn () => OrdersResource::collection($orders)
                    : Inertia::lazy(fn () => OrdersResource::collection($orders)),


            ]
        )->table($this->tableStructure($this->parent, OrdersTabsEnum::ORDERS->value));
    }


    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(OrdersTabsEnum::values());

        return $this->handle(parent: $organisation);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(OrdersTabsEnum::values());

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
            'grp.org.shops.show.ordering.orders.index' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.ordering.orders.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
