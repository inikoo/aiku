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
// use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\CRM\Customer\UI\ShowCustomerClient;
use App\Actions\Dispatching\DeliveryNote\UI\IndexDeliveryNotes;
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
        return $this->handle($order)->withTab;
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

            $timeline[$state->value] = [
                'label'   => $state->labels()[$state->value],
                'tooltip' => $state->labels()[$state->value],
                'key'     => $state->value,
               /*  'icon'      => $palletDelivery->state->stateIcon()[$state->value]['icon'], */
                'timestamp' => $order->{$state->snake() . '_at'} ? $order->{$state->snake() . '_at'}->toISOString() : null
            ];
        }

        $finalTimeline = Arr::except(
            $timeline,
            [$order->state->value == OrderStateEnum::CANCELLED->value
                ? OrderStateEnum::DISPATCHED->value
                : OrderStateEnum::CANCELLED->value]
        );

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
                            'route'   => [  // TODO
                                'name'       => 'grp.profile.show',
                                'parameters' => [

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
                    'products_list' => [  // TODO
                        'name'       => 'grp.json.shop.catalogue.order.products',
                        'parameters' => [
                            'shop' => $order->shop->slug,
                            'scope' => $order->slug
                        ]
                    ]
                ],
                'timeline'      => $finalTimeline,

                'box_stats'     => [
                    'customer' => [
                        'slug'         => 'airhead-designs-ltd',
                        'reference'    => '415850',
                        'name'         => 'airHEAD Designs Ltd',
                        'contact_name' => 'Holly Galbraith',
                        'company_name' => 'airHEAD Designs Ltd',
                        'location'     => [
                            'GB',
                            'United Kingdom',
                            'London'
                        ],
                        'address' => [
                            'id'                  => 711,
                            'address_line_1'      => 'Studio 19',
                            'address_line_2'      => 'Grow Studios, 86 Wallis Road, Main Yard',
                            'sorting_code'        => '',
                            'postal_code'         => 'E9 5LN',
                            'locality'            => 'London',
                            'dependent_locality'  => '',
                            'administrative_area' => '',
                            'country_code'        => 'GB',
                            'country_id'          => 48,
                            'checksum'            => 'dcd0872437dca2150658f0db835a67e0',
                            'created_at'          => '2024-08-22T21:04:42.000000Z',
                            'updated_at'          => '2024-08-22T21:04:42.000000Z',
                            'country'             => [
                                'code' => 'GB',
                                'iso3' => 'GBR',
                                'name' => 'United Kingdom'
                            ],
                            'formatted_address' => '<p translate="no"><span class="address-line1">Studio 19</span><br><span class="address-line2">Grow Studios, 86 Wallis Road, Main Yard</span><br><span class="locality">London</span><br><span class="postal-code">E9 5LN</span><br><span class="country">United Kingdom</span></p>',
                            'can_edit'          => null,
                            'can_delete'        => null
                        ],
                        'email'      => 'accounts@ventete.com',
                        'phone'      => '+447725269253',
                        'created_at' => '2021-12-01T09:46:06.000000Z'
                    ],
                    'delivery_status' => [
                        'tooltip' => 'In process',
                        'icon'    => 'fal fa-seedling',
                        'class'   => 'text-lime-500',
                        'color'   => 'lime',
                        'app'     => [
                            'name' => 'seedling',
                            'type' => 'font-awesome-5'
                        ]
                    ],
                    'order_summary' => [
                        [
                            [
                                'label'       => 'Items',
                                'quantity'    => 2,
                                'price_base'  => 'Multiple',
                                'price_total' => '3.20'
                            ],
                        ],
                        [
                            [
                                'label'       => 'Charges',
                                'information' => '',
                                'price_total' => '3.20'
                            ],
                            [
                                'label'       => 'Shipping',
                                'information' => '',
                                'price_total' => '0.64'
                            ]
                        ],
                        [
                            [
                                'label'       => 'Net',
                                'information' => '',
                                'price_total' => '3.20'
                            ],
                            [
                                'label'       => 'Tax 20%',
                                'information' => '',
                                'price_total' => '0.64'
                            ]
                        ],
                        [
                            [
                                'label'       => 'Total',
                                'price_total' => '3.84'
                            ]
                        ],
                    ]
                ],
                'currency' => [
                    'data' => [
                        'id'     => 23,
                        'code'   => 'GBP',
                        'name'   => 'British Pound',
                        'symbol' => '£'
                    ]
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
