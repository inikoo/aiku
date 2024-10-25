<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order\UI;

use App\Actions\Accounting\Invoice\UI\IndexInvoices;
use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\CRM\Customer\UI\ShowCustomerClient;
use App\Actions\CRM\Customer\UI\WithCustomerSubNavigation;
use App\Actions\Dispatching\DeliveryNote\UI\IndexDeliveryNotes;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\UI\Ordering\OrdersTabsEnum;
use App\Http\Resources\Accounting\InvoicesResource;
use App\Http\Resources\Dispatching\DeliveryNotesResource;
use App\Http\Resources\Ordering\OrdersResource;
use App\Http\Resources\Sales\OrderResource;
use App\InertiaTable\InertiaTable;
use App\Models\CRM\Customer;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Shop;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Dropshipping\ShopifyUser;
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
    use WithCustomerSubNavigation;

    private Organisation|Shop|Customer|CustomerClient|Asset|ShopifyUser $parent;

    protected function getElementGroups(Organisation|Shop|Customer|CustomerClient|Asset $parent): array
    {
        return [
            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    OrderStateEnum::labels(),
                    OrderStateEnum::count($parent)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('orders.state', $elements);
                }
            ],


        ];
    }

    public function handle(Organisation|Shop|Customer|CustomerClient|Asset|ShopifyUser $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('orders.reference', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }


        $query = QueryBuilder::for(Order::class);

        if (class_basename($parent) == 'Shop') {
            $query->where('orders.shop_id', $parent->id);
        } elseif (class_basename($parent) == 'Customer') {
            $query->where('orders.customer_id', $parent->id);
        } elseif (class_basename($parent) == 'CustomerClient') {
            $query->where('orders.customer_client_id', $parent->id);
        }

        $query->leftJoin('customers', 'orders.customer_id', '=', 'customers.id');
        $query->leftJoin('customer_clients', 'orders.customer_client_id', '=', 'customer_clients.id');

        $query->leftJoin('model_has_payments', function ($join) {
            $join->on('orders.id', '=', 'model_has_payments.model_id')
                ->where('model_has_payments.model_type', '=', 'Order');
        })
            ->leftJoin('payments', 'model_has_payments.payment_id', '=', 'payments.id');

        $query->leftJoin('currencies', 'orders.currency_id', '=', 'currencies.id');

        if (!($parent instanceof ShopifyUser)) {
            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $query->whereElementGroup(
                    key: $key,
                    allowedElements: array_keys($elementGroup['elements']),
                    engine: $elementGroup['engine'],
                    prefix: $prefix
                );
            }
        }

        if ($parent instanceof ShopifyUser) {
            $query->join('shopify_user_has_fulfilments', function ($join) use ($parent) {
                $join->on('orders.id', '=', 'shopify_user_has_fulfilments.order_id')
                    ->where('shopify_user_has_fulfilments.shopify_user_id', '=', $parent->id);
            });
        }

        return $query->defaultSort('orders.reference')
            ->select([
                'orders.reference',
                'orders.date',
                'orders.state',
                'orders.created_at',
                'orders.updated_at',
                'orders.slug',
                'orders.net_amount',
                'orders.total_amount',
                'orders.state',
                'customers.name as customer_name',
                'customers.slug as customer_slug',
                'customer_clients.name as client_name',
                'customer_clients.ulid as client_ulid',
                'payments.state as payment_state',
                'payments.status as payment_status',
                'shops.slug as shop_slug',
                'currencies.code as currency_code',
                'currencies.id as currency_id',
            ])
            ->leftJoin('order_stats', 'orders.id', 'order_stats.order_id')
            ->leftJoin('shops', 'orders.shop_id', 'shops.id')
            ->allowedSorts(['reference', 'date'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|Shop|Customer|CustomerClient|Asset|ShopifyUser $parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            if ($prefix) {
                InertiaTable::updateQueryBuilderParameters($prefix);
            }


            $noResults = __("No orders found");
            if ($parent instanceof Customer) {
                $stats     = $parent->stats;
                $noResults = __("Customer has no orders");
            } elseif ($parent instanceof CustomerClient) {
                $stats     = $parent->stats;
                $noResults = __("This customer client hasn't place any orders");
            } else {
                $stats = $parent->salesStats;
            }


            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title' => $noResults,
                        'count' => $stats->number_orders ?? 0
                    ]
                );

            if (!($parent instanceof ShopifyUser)) {
                foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                    $table->elementGroup(
                        key: $key,
                        label: $elementGroup['label'],
                        elements: $elementGroup['elements']
                    );
                }
            }

            $table->column(key: 'state', label: __('state'), canBeHidden: false, searchable: true);
            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true);
            if ($parent instanceof Shop) {
                $table->column(key: 'customer_name', label: __('customer'), canBeHidden: false, searchable: true);
            }
            $table->column(key: 'payment_status', label: __('payment'), canBeHidden: false, searchable: true);
            $table->column(key: 'net_amount', label: __('net'), canBeHidden: false, searchable: true, type: 'currency');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Customer or $this->parent instanceof CustomerClient) {
            $this->canEdit = $request->user()->hasPermissionTo("crm.{$this->shop->id}.view");

            return $request->user()->hasPermissionTo("crm.{$this->organisation->id}.view");
        }

        $this->canEdit = $request->user()->hasPermissionTo("orders.{$this->shop->id}.view");

        return $request->user()->hasPermissionTo("orders.{$this->shop->id}.view");
    }

    public function jsonResponse(LengthAwarePaginator $orders): AnonymousResourceCollection
    {
        return OrderResource::collection($orders);
    }

    public function htmlResponse(LengthAwarePaginator $orders, ActionRequest $request): Response
    {
        $subNavigation = null;
        if ($this->parent instanceof CustomerClient) {
            $subNavigation = $this->getCustomerClientSubNavigation($this->parent, $request);
        } elseif ($this->parent instanceof Customer) {
            if ($this->parent->is_dropshipping) {
                $subNavigation = $this->getCustomerDropshippingSubNavigation($this->parent, $request);
            } else {
                $subNavigation = $this->getCustomerSubNavigation($this->parent, $request);
            }
        }
        $title      = __('Orders');
        $model      = '';
        $icon       = [
            'icon'  => ['fal', 'fa-shopping-cart'],
            'title' => __('orders')
        ];
        $afterTitle = null;
        $iconRight  = null;
        $actions    = null;

        if ($this->parent instanceof CustomerClient) {
            $title      = $this->parent->name;
            $model      = __('customer client');
            $icon       = [
                'icon'  => ['fal', 'fa-folder'],
                'title' => __('customer client')
            ];
            $iconRight  = [
                'icon' => 'fal fa-shopping-cart',
            ];
            $afterTitle = [
                'label' => __('Orders')
            ];
            $actions    = [
                [
                    'type'        => 'button',
                    'style'       => 'create',
                    'label'       => 'Add order',
                    'key'         => 'add_order',
                    'fullLoading' => true,
                    'route'       => [
                        'method'     => 'post',
                        'name'       => 'grp.models.customer-client.order.store',
                        'parameters' => [
                            'customerClient' => $this->parent->id
                        ]
                    ],
                ],
            ];
        } elseif ($this->parent instanceof Customer) {
            $title = $this->parent->name;

            $icon       = [
                'icon'  => ['fal', 'fa-user'],
                'title' => __('customer')
            ];
            $iconRight  = [
                'icon' => 'fal fa-shopping-cart',
            ];
            $afterTitle = [
                'label' => __('Orders')
            ];

            if ($this->shop->type == ShopTypeEnum::B2B) {
                $actions = [
                    [
                        'type'        => 'button',
                        'style'       => 'create',
                        'label'       => 'Add order',
                        'key'         => 'add_order',
                        'fullLoading' => true,
                        'route'       => [
                            'method'     => 'post',
                            'name'       => 'grp.models.customer.order.store',
                            'parameters' => [
                                'customer' => $this->parent->id
                            ]
                        ]
                    ],
                ];
            }
        }

        if ($this->parent instanceof Shop) {
            $shop = $this->parent;
        } else {
            $shop = $this->parent->shop;
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
                    'subNavigation' => $subNavigation,
                    'actions'       => $actions
                ],
                'data'        => OrderResource::collection($orders),

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => OrdersTabsEnum::navigation(),
                ],

                OrdersTabsEnum::STATS->value => $this->tab == OrdersTabsEnum::STATS->value ?
                    fn () => GetOrderStats::run($shop)
                    : Inertia::lazy(fn () => GetOrderStats::run($shop)),

                OrdersTabsEnum::ORDERS->value => $this->tab == OrdersTabsEnum::ORDERS->value ?
                    fn () => OrdersResource::collection($orders)
                    : Inertia::lazy(fn () => OrdersResource::collection($orders)),


                OrdersTabsEnum::INVOICES->value => $this->tab == OrdersTabsEnum::INVOICES->value ?
                    fn () => InvoicesResource::collection(IndexInvoices::run($this->parent))
                    : Inertia::lazy(fn () => InvoicesResource::collection(IndexInvoices::run($this->parent))),


                OrdersTabsEnum::DELIVERY_NOTES->value => $this->tab == OrdersTabsEnum::DELIVERY_NOTES->value ?
                    fn () => DeliveryNotesResource::collection(IndexDeliveryNotes::run($this->parent))
                    : Inertia::lazy(fn () => DeliveryNotesResource::collection(IndexDeliveryNotes::run($this->parent))),


            ]
        )->table($this->tableStructure($this->parent, OrdersTabsEnum::ORDERS->value))
            ->table(IndexInvoices::make()->tableStructure(parent: $this->parent, prefix: OrdersTabsEnum::INVOICES->value))
                ->table(IndexDeliveryNotes::make()->tableStructure($this->parent));
    }

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(OrdersTabsEnum::values());

        return $this->handle(parent: $organisation, prefix: OrdersTabsEnum::ORDERS->value);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(OrdersTabsEnum::values());

        return $this->handle(parent: $shop, prefix: OrdersTabsEnum::ORDERS->value);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inCustomer(Organisation $organisation, Shop $shop, Customer $customer, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $customer;
        $this->initialisationFromShop($shop, $request)->withTab(OrdersTabsEnum::values());

        return $this->handle(parent: $customer, prefix: OrdersTabsEnum::ORDERS->value);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inCustomerClient(Organisation $organisation, Shop $shop, Customer $customer, CustomerClient $customerClient, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $customerClient;
        $this->initialisationFromShop($shop, $request)->withTab(OrdersTabsEnum::values());

        return $this->handle(parent: $customerClient, prefix: OrdersTabsEnum::ORDERS->value);
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
