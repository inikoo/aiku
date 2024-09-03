<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order\UI;

// use App\Actions\Accounting\Invoice\UI\IndexInvoices;
// use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\CRM\Customer\UI\ShowCustomerClient;
use App\Actions\Dispatching\DeliveryNote\UI\IndexDeliveryNotes;
use App\Actions\Helpers\Country\UI\GetAddressData;
use App\Actions\Ordering\Transaction\UI\IndexTransactions;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasOrderingAuthorisation;
use App\Enums\UI\Ordering\OrderTabsEnum;
// use App\Http\Resources\Accounting\InvoicesResource;
// // use App\Http\Resources\Dispatching\DeliveryNoteResource;
use App\Http\Resources\Ordering\TransactionsResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Ordering\Order;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Http\Resources\CRM\CustomerResource;
use App\Http\Resources\Helpers\AddressResource;
use App\Http\Resources\Helpers\CurrencyResource;
use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;

class ShowOrder extends OrgAction
{
    use HasOrderingAuthorisation;
    private Shop|Customer|CustomerClient $parent;

    public function handle(Order $order): Order
    {
        return $order;
    }


    public function inOrganisation(Organisation $organisation, Order $order, ActionRequest $request): Order
    {
        $this->scope = $organisation;
        $this->initialisation($organisation, $request)->withTab(OrderTabsEnum::values());

        return $this->handle($order);
    }

    public function asController(Organisation $organisation, Shop $shop, Order $order, ActionRequest $request): Order
    {
        $this->parent = $shop;
        $this->scope  = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(OrderTabsEnum::values());
        return $this->handle($order);
    }

    public function inCustomerInShop(Organisation $organisation, Shop $shop, Customer $customer, Order $order, ActionRequest $request): Order
    {
        $this->parent = $customer;
        $this->scope  = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(OrderTabsEnum::values());
        return $this->handle($order);
    }

    public function inCustomerClient(Organisation $organisation, Shop $shop, Customer $customer, CustomerClient $customerClient, Order $order, ActionRequest $request): Order
    {
        $this->parent = $customerClient;
        $this->scope  = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(OrderTabsEnum::values());
        return $this->handle($order);
    }


    public function htmlResponse(Order $order, ActionRequest $request): Response
    {

        $timeline       = [];
        foreach (OrderStateEnum::cases() as $state) {

            $timestamp = $order->{$state->snake() . '_at'} 
            ? $order->{$state->snake() . '_at'} 
            : null;
        
        // If all possible values are null, set the timestamp to null explicitly
        $timestamp = $timestamp ?: null;
        
        $timeline[$state->value] = [
            'label'     => $state->labels()[$state->value],
            'tooltip'   => $state->labels()[$state->value],
            'key'       => $state->value,
            /* 'icon'    => $palletDelivery->state->stateIcon()[$state->value]['icon'], */
            'timestamp' => $timestamp
        ];
        }

        $finalTimeline = Arr::except(
            $timeline,
            [$order->state->value == OrderStateEnum::CANCELLED->value
                ? OrderStateEnum::DISPATCHED->value
                : OrderStateEnum::CANCELLED->value]
        );

        $addresses = $order->customer->addresses;

        $processedAddresses = $addresses->map(function ($address) {


            if(!DB::table('model_has_addresses')->where('address_id', $address->id)->where('model_type', '=', 'Customer')->exists()) {

                return $address->setAttribute('can_delete', false)
                    ->setAttribute('can_edit', true);
            }


            return $address->setAttribute('can_delete', true)
                            ->setAttribute('can_edit', true);
        });

        $customerAddressId              = $order->customer->address->id;
        $customerDeliveryAddressId      = $order->customer->deliveryAddress->id;
        $orderDeliveryAddressIds        = Order::where('customer_id', $order->customer_id)
                                            ->pluck('delivery_address_id')
                                            ->unique()
                                            ->toArray();

        $forbiddenAddressIds = array_merge(
            $orderDeliveryAddressIds,
            [$customerAddressId, $customerDeliveryAddressId]
        );

        $processedAddresses->each(function ($address) use ($forbiddenAddressIds) {
            if (in_array($address->id, $forbiddenAddressIds, true)) {
                $address->setAttribute('can_delete', false)
                        ->setAttribute('can_edit', true);
            }
        });

        $addressCollection = AddressResource::collection($processedAddresses);

        $payAmount   = $order->total_amount - $order->payment_amount;
        $roundedDiff = round($payAmount, 2);

        return Inertia::render(
            'Org/Ordering/Order',
            [
                'title'       => __('order'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $order,
                    $request->route()->getName(),
                    $request->route()->originalParameters(),
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($order, $request),
                    'next'     => $this->getNext($order, $request),
                ],
                'pageHead'    => [
                    'title'     => $order->reference,
                    'model'     => __('Order'),
                    'icon'      => [
                        'icon'  => 'fal fa-shopping-cart',
                        'title' => __('customer client')
                    ],
                    'actions'   => [
                        [
                            'type'    => 'button',
                            'style'   => 'secondary',
                            'icon'    => 'fal fa-plus',
                            'key'     => 'add-products',
                            'label'   => __('add products'),
                            'tooltip' => __('Add products'),
                            'route'   => [
                                'name'       => 'grp.models.order.transaction.store',
                                'parameters' => [
                                    'order' => $order->id,
                                ]
                            ]
                        ]
                    ]
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => OrderTabsEnum::navigation()
                ],
                'routes'    => [
                    'updateOrderRoute' => [
                        'method'     => 'patch',
                        'name'       => 'grp.models.order.update',
                        'parameters' => [
                            'order' => $order->id,
                        ]
                    ],
                    'products_list' => [
                        'name'       => 'grp.json.shop.catalogue.order.products',
                        'parameters' => [
                            'shop'  => $order->shop->slug,
                            'scope' => $order->slug
                        ]
                    ]
                ],
                'alert'   => [  // TODO
                    'status'        => 'danger',
                    'title'         => 'Dummy Alert from BE',
                    'description'   => 'Dummy description'
                ],
                'notes' => [  // TODO
                    "note_list"  => [
                        [ "label" => __("Customer"), "note" => "", "editable" => false, "bgColor" => "silver", "field" => "customer_notes" ],
                        [ "label" => __("Public"), "note" => "", "editable" => true, "bgColor" => "#40E0D0", "field" => "public_notes" ],
                        [ "label" => __("Private"), "note" => "", "editable" => true, "bgColor" => "#4b5563", "textColor" => "#fff", "field" => "internal_notes" ]
                    ],
                    "updateRoute"   => [
                        "name"       => "grp.models.pallet-delivery.update",
                        "parameters" => [
                            "palletDelivery" => 1
                        ]
                    ]
                ],
                'timelines'      => $finalTimeline,

                'box_stats'     => [
                    'customer'          => array_merge(
                        CustomerResource::make($order->customer)->getArray(),
                        [
                            'addresses'      => [
                                'value'   => AddressResource::make($order->deliveryAddress ?? new Address()),
                                'options' => [
                                    'countriesAddressData' => GetAddressData::run()
                                ],
                                'address_list'                   => $addressCollection,
                                'pinned_address_id'              => $order->customer->delivery_address_id,
                                'home_address_id'                => $order->customer->address_id,
                                'current_selected_address_id'    => $order->delivery_address_id,
                                'selected_delivery_addresses_id' => $orderDeliveryAddressIds,
                                'routes_list'                    => [
                                    'pinned_route'                   => [
                                        'method'     => 'patch',
                                        'name'       => 'grp.models.customer.delivery-address.update',
                                        'parameters' => [
                                            'customer' => $order->customer_id
                                        ]
                                    ],
                                    'delete_route'  => [
                                        'method'     => 'delete',
                                        'name'       => 'grp.models.customer.delivery-address.delete',
                                        'parameters' => [
                                            'customer' => $order->customer_id
                                        ]
                                    ],
                                    'store_route' => [
                                        'method'      => 'post',
                                        'name'        => 'grp.models.customer.address.store',
                                        'parameters'  => [
                                            'customer' => $order->customer_id
                                        ]
                                    ],
                                ]
                            ],
                        ]
                    ),
                    'products'  => [
                        'payment'  => [
                            'routes' => [
                                'fetch_payment_accounts' => [
                                    'name'       => 'grp.json.shop.payment-accounts',
                                    'parameters' => [
                                        'shop' => $order->shop->slug
                                    ]
                                ],
                                'submit_payment' => [
                                    'name'       => 'grp.models.customer.payment.order.store',
                                    'parameters' => [
                                        'customer' => $order->customer_id,
                                        'scope'    => $order->id
                                    ]
                                ]

                            ],
                            'total_amount'=> $order->total_amount,
                            'paid_amount' => $order->payment_amount,
                            'pay_amount'  => $roundedDiff,
                        ],
                        'estimated_weight' => $order->estimated_weight ?? 0
                    ],

                    // 'delivery_status' => OrderStateEnum::stateIcon($order->state->value),
                    'order_summary'   => [
                        [
                            [
                                'label'       => 'Items',
                                'quantity'    => $order->stats->number_transactions,
                                'price_base'  => 'Multiple',
                                'price_total' => $order->net_amount
                            ],
                        ],
                        [
                            [
                                'label'       => 'Charges',
                                'information' => '',
                                'price_total' => '0'
                            ],
                            [
                                'label'       => 'Shipping',
                                'information' => '',
                                'price_total' => '0'
                            ]
                        ],
                        [
                            [
                                'label'       => 'Net',
                                'information' => '',
                                'price_total' => $order->net_amount
                            ],
                            [
                                'label'       => 'Tax 20%',
                                'information' => '',
                                'price_total' => $order->tax_amount
                            ]
                        ],
                        [
                            [
                                'label'       => 'Total',
                                'price_total' => $order->total_amount
                            ]
                        ],
                        'currency' => CurrencyResource::make($order->currency),

                    ],
                ],
                'data'       => OrderResource::make($order),
                // 'showcase'=> GetOrderShowcase::run($order),



                OrderTabsEnum::PRODUCTS->value => $this->tab == OrderTabsEnum::PRODUCTS->value ?
                    fn () => TransactionsResource::collection(IndexTransactions::run($order))
                    : Inertia::lazy(fn () => TransactionsResource::collection(IndexTransactions::run($order))),

                // OrderTabsEnum::INVOICES->value => $this->tab == OrderTabsEnum::INVOICES->value ?
                //     fn () => InvoicesResource::collection(IndexInvoices::run($order))
                //     : Inertia::lazy(fn () => InvoicesResource::collection(IndexInvoices::run($order))),

                // OrderTabsEnum::DELIVERY_NOTES->value => $this->tab == OrderTabsEnum::DELIVERY_NOTES->value ?
                //     fn () => DeliveryNoteResource::collection(IndexDeliveryNotes::run($order))
                //     : Inertia::lazy(fn () => DeliveryNoteResource::collection(IndexDeliveryNotes::run($order))),

            ]
        )
        ->table(IndexTransactions::make()->tableStructure($order));
        //     ->table(IndexInvoices::make()->tableStructure($order))
        //     ->table(IndexDeliveryNotes::make()->tableStructure($order));
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->hasPermissionTo('hr.edit'));
        $this->set('canViewUsers', $request->user()->hasPermissionTo('users.view'));
    }

    public function jsonResponse(Order $order): OrderResource
    {
        return new OrderResource($order);
    }

    public function getBreadcrumbs(Order $order, string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (Order $order, array $routeParameters, string $suffix) {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Orders')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $order->reference,
                        ],

                    ],
                    'suffix'         => $suffix

                ],
            ];
        };

        return match ($routeName) {



            'grp.org.shops.show.ordering.orders.show',
            'grp.org.shops.show.ordering.orders.edit'
            => array_merge(
                (new ShowShop())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $order,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.ordering.orders.index',
                            'parameters' => Arr::except($routeParameters, ['order'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.ordering.orders.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.crm.customers.show.orders.show'
            => array_merge(
                (new ShowCustomer())->getBreadcrumbs('grp.org.shops.show.crm.customers.show', $routeParameters),
                $headCrumb(
                    $order,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.crm.customers.show.orders.index',
                            'parameters' => Arr::except($routeParameters, ['order'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.crm.customers.show.orders.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.crm.customers.show.customer-clients.orders.show'
            => array_merge(
                (new ShowCustomerClient())->getBreadcrumbs('grp.org.shops.show.crm.customers.show.customer-clients.show', $routeParameters),
                $headCrumb(
                    $order,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.crm.customers.show.customer-clients.orders.index',
                            'parameters' => Arr::except($routeParameters, ['order'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.crm.customers.show.customer-clients.orders.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(Order $order, ActionRequest $request): ?array
    {
        $previous = Order::where('reference', '<', $order->reference)->when(true, function ($query) use ($order, $request) {
            if ($request->route()->getName() == 'shops.show.orders.show') {
                $query->where('orders.shop_id', $order->shop_id);
            }
        })->orderBy('reference', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Order $order, ActionRequest $request): ?array
    {
        $next = Order::where('reference', '>', $order->reference)->when(true, function ($query) use ($order, $request) {
            if ($request->route()->getName() == 'shops.show.orders.show') {
                $query->where('orders.shop_id', $order->shop_id);
            }
        })->orderBy('reference')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Order $order, string $routeName): ?array
    {
        if (!$order) {
            return null;
        }


        return match ($routeName) {
            'grp.org.shops.show.ordering.orders.show' => [
                'label' => $order->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'  => $this->organisation->slug,
                        'shop'          => $order->shop->slug,
                        'order'         => $order->slug
                    ]

                ]
            ],
            'grp.org.shops.show.crm.customers.show.orders.show' => [
                'label' => $order->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'  => $this->organisation->slug,
                        'shop'          => $order->shop->slug,
                        'customer'      => $this->parent->slug,
                        'order'         => $order->slug
                    ]

                ]
            ],
            'grp.org.shops.show.crm.customers.show.customer-clients.orders.show' => [
                'label' => $order->reference,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'   => $this->organisation->slug,
                        'shop'           => $order->shop->slug,
                        'customer'       => $this->parent->customer->slug,
                        'customerClient' => $this->parent->ulid,
                        'order'          => $order->slug
                    ]

                ]
            ]
        };
    }
}
