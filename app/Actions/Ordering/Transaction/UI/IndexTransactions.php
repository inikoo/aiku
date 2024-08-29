<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Transaction\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\CRM\Customer\UI\ShowCustomerClient;
use App\Actions\CRM\Customer\UI\WithCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Enums\UI\Ordering\OrdersTabsEnum;
use App\Http\Resources\Ordering\OrdersResource;
use App\Http\Resources\Ordering\TransactionsResource;
use App\Http\Resources\Sales\OrderResource;
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\Invoice;
use App\Models\CRM\Customer;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Shop;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexTransactions extends OrgAction
{
    private Organisation|Shop|Customer|Order|Invoice|Asset $parent;

    public function handle(Organisation|Shop|Customer|Order|Invoice|Asset $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('assets.code', '~*', "\y$value\y")
                    ->orWhereStartWith('assets.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query=QueryBuilder::for(Transaction::class);

        if (class_basename($parent) == 'Organisation') {
            $query->where('transactions.organisation_', $parent->id);
        } elseif (class_basename($parent) == 'Shop') {
            $query->where('transactions.shop_id', $parent->id);
        } elseif (class_basename($parent) == 'Customer') {
            $query->where('transactions.customer_id', $parent->id);
        } elseif (class_basename($parent) == 'Order') {
            $query->where('transactions.order_id', $parent->id);
        } elseif (class_basename($parent) == 'Invoice') {
            $query->where('transactions.invoice_id', $parent->id);
        } elseif (class_basename($parent) == 'Asset') {
            $query->where('transactions.asset_id', $parent->id);
        }

        $query->leftjoin('assets', 'transactions.asset_id', '=', 'assets.id');

        return $query->defaultSort('transactions.id')
            ->select([
                'transactions.id',
                'transactions.state',
                'transactions.status',
                'transactions.quantity_ordered',
                'transactions.quantity_bonus',
                'transactions.quantity_dispatched',
                'transactions.quantity_fail',
                'transactions.quantity_cancelled',
                'transactions.gross_amount',
                'transactions.net_amount',
                'transactions.created_at',
                'assets.code as asset_code',
                'assets.name as asset_name',
                'assets.type as asset_type',
            ])
            ->allowedSorts(['asset_code', 'asset_name', 'net_amount', 'quantity_ordered' ])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|Shop|Customer|Order|Invoice|Asset $parent, $prefix = null): Closure
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
                        'title' => __("No transactions found"),
                    ]
                );

            $table->column(key: 'asset_code', label: __('Code'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'asset_name', label: __('Name'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'quantity_ordered', label: __('Quantity'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'net_amount', label: __('Net'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("orders.{$this->shop->id}.view");

        return $request->user()->hasPermissionTo("orders.{$this->shop->id}.view");
    }


    public function jsonResponse(LengthAwarePaginator $transactions): AnonymousResourceCollection
    {
        return TransactionsResource::collection($transactions);
    }


    public function htmlResponse(LengthAwarePaginator $transactions, ActionRequest $request): Response
    {

        $title = __('Transactions');
        $model = __('transaction');
        $icon  = [
            'icon'  => ['fal', 'fa-shopping-cart'],
            'title' => __('transaction')
        ];
        $afterTitle=null;
        $iconRight =null;
        $actions   = null;

        if ($this->parent instanceof Shop) {
            $title = $this->parent->name;
            $model = __('shop');
            $icon  = [
                'icon'  => ['fal', 'fa-folder'],
                'title' => __('shop')
            ];
            $iconRight    =[
                'icon' => 'fal fa-shopping-cart',
            ];
            $afterTitle= [
                'label'     => __('Transactions')
            ];
        } elseif ($this->parent instanceof Customer) {
            $title = $this->parent->name;
            $model = __('customer');
            $icon  = [
                'icon'  => ['fal', 'fa-user'],
                'title' => __('customer')
            ];
            $iconRight    =[
                'icon' => 'fal fa-shopping-cart',
            ];
            $afterTitle= [
                'label'     => __('Transactions')
            ];
        }
        return Inertia::render(
            'Ordering/Orders',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('orders'),
                'pageHead'    => [
                    'title'         => $title,
                    'icon'          => $icon,
                    'model'         => $model,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                    'actions'       => $actions
                ],
                'data'        => TransactionsResource::collection($transactions),

                // 'tabs'        => [
                //     'current'    => $this->tab,
                //     'navigation' => OrdersTabsEnum::navigation(),
                // ],

                // OrdersTabsEnum::ORDERS->value => $this->tab == OrdersTabsEnum::ORDERS->value ?
                //     fn () => OrdersResource::collection($orders)
                //     : Inertia::lazy(fn () => OrdersResource::collection($orders)),


            ]
        );
        // ->table($this->tableStructure($this->parent, OrdersTabsEnum::ORDERS->value));
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
                        'label' => __('Orders'),
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
            'grp.org.shops.show.crm.customers.show.orders.index' =>
            array_merge(
                ShowCustomer::make()->getBreadcrumbs('grp.org.shops.show.crm.customers.show', $routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.crm.customers.show.orders.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            'grp.org.shops.show.crm.customers.show.customer-clients.orders.index' =>
            array_merge(
                ShowCustomerClient::make()->getBreadcrumbs('grp.org.shops.show.crm.customers.show.customer-clients.show', $routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.crm.customers.show.customer-clients.orders.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
