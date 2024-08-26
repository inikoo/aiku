<?php
/*
 * Author: Vika Aqordi <aqordivika@yahoo.co.id>
 * Created on: 26-08-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

namespace App\Actions\CRM\Customer\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Actions\Traits\WithWebUserMeta;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\UI\CRM\CustomerTabsEnum;
use App\Http\Resources\CRM\CustomerClientResource;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\CustomerClient;
use App\Models\SysAdmin\Organisation;
use Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowCustomerOrder extends OrgAction
{
    use WithActionButtons;
    use WithWebUserMeta;
    use WithCustomerSubNavigation;

    private Customer $parent;

    public function handle(CustomerClient $customerClient): CustomerClient
    {
        return $customerClient;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.view");
    }

    // public function inOrganisation(Organisation $organisation, Customer $customer, ActionRequest $request): Customer
    // {
    //     $this->parent = $organisation;
    //     $this->initialisation($organisation, $request)->withTab(CustomerTabsEnum::values());

    //     return $this->handle($customer);
    // }


    public function asController(
        Organisation $organisation,
        Shop $shop,
        Customer $customer,
        CustomerClient $customerClient,
        ActionRequest $request
    ): CustomerClient {
        $this->parent = $customer;
        $this->initialisationFromShop($shop, $request)->withTab(CustomerTabsEnum::values());

        return $this->handle($customerClient);
    }


    public function htmlResponse(CustomerClient $customerClient, ActionRequest $request): Response
    {

        $shopMeta = [];

        if ($request->route()->getName() == 'customers.show') {
            $shopMeta = [
                'href'     => ['shops.show', $customerClient->customer->shop->slug],
                'name'     => $customerClient->customer->shop->code,
                'leftIcon' => [
                    'icon'    => 'fal fa-store-alt',
                    'tooltip' => __('Shop'),
                ],
            ];
        }
        // $subNavigation = null;
        // if ($this->parent instanceof Shop) {
        //     if ($this->parent->type == ShopTypeEnum::DROPSHIPPING) {
        //         $subNavigation = $this->getCustomerSubNavigation($customerClient->customer, $request);
        //     }
        // }


        return Inertia::render(
            'Org/Shop/CRM/Order',
            [
                'title'       => __('customer client'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation' => [
                    'previous' => $this->getPrevious($customerClient, $request),
                    'next'     => $this->getNext($customerClient, $request),
                ],
                'pageHead' => [
                    'title'     => 'xxxxxxxxxxx',
                    'model'     => __('Order'),
                    'icon'      => [
                        'icon'  => 'fal fa-shopping-cart',
                        'title' => __('customer client')
                    ],
                    'meta' => array_filter([
                        $shopMeta,
                    ]),
                    // 'actions' => [
                    //     $this->canDelete ? $this->getDeleteActionIcon($request) : null,
                    //     $this->canEdit ? $this->getEditActionIcon($request) : null,
                    //     [
                    //         'type'    => 'button',
                    //         'style'   => 'create',
                    //         'label'   => 'Add order',
                    //         'key'     => 'addorder',
                    //         'route'   => [
                    //             'name'       => 'grp.models.pallet-delivery.multiple-pallets.store',
                    //             'parameters' => [
                    //                 'palletDelivery' => 3
                    //             ]
                    //         ]
                    //     ],
                    // ],
                ],
                'box_stats'     => [
                    'fulfilment_customer' => [
                        'radioTabs' => [
                            'pallets_storage' => true,
                            'items_storage'   => false,
                            'dropshipping'    => true
                        ],
                        'number_pallets'                => 26,
                        'number_pallets_state_received' => 0,
                        'number_stored_items'           => 0,
                        'number_pallet_deliveries'      => 2,
                        'number_pallet_returns'         => 0,
                        'slug'                          => 'airhead-designs-ltd',
                        'fulfilment'                    => [
                            'slug' => 'awf',
                            'name' => 'AW Fulfilment'
                        ],
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
                        ]
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
                                'label'       => 'Services',
                                'quantity'    => 2,
                                'price_base'  => 'Multiple',
                                'price_total' => '3.20'
                            ],
                            [
                                'label'       => 'Physical Goods',
                                'quantity'    => 0,
                                'price_base'  => 'Multiple',
                                'price_total' => '0.00'
                            ]
                        ],
                        [],
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
                        'currency' => [
                            'data' => [
                                'id'     => 23,
                                'code'   => 'GBP',
                                'name'   => 'British Pound',
                                'symbol' => '£'
                            ]
                        ]
                    ]
                ],
                'data'  => [
                    'data'  => [
                        'id'            => 7,
                        'customer_name' => 'airHEAD Designs Ltd',
                        'reference'     => 'ADL-002',
                        'state'         => 'in-process',
                        'timeline'      => [
                            'in-process' => [
                                'label'     => 'In Process',
                                'tooltip'   => 'In Process',
                                'key'       => 'in-process',
                                'timestamp' => '2024-08-25T18:13:50.000000Z'
                            ],
                            'submitted' => [
                                'label'     => 'Submitted',
                                'tooltip'   => 'Submitted',
                                'key'       => 'submitted',
                                'timestamp' => null
                            ],
                            'confirmed' => [
                                'label'     => 'Confirmed',
                                'tooltip'   => 'Confirmed',
                                'key'       => 'confirmed',
                                'timestamp' => null
                            ],
                            'received' => [
                                'label'     => 'Received',
                                'tooltip'   => 'Received',
                                'key'       => 'received',
                                'timestamp' => null
                            ],
                            'booking-in' => [
                                'label'     => 'Booking In',
                                'tooltip'   => 'Booking In',
                                'key'       => 'booking-in',
                                'timestamp' => null
                            ],
                            'booked-in' => [
                                'label'     => 'Booked In',
                                'tooltip'   => 'Booked In',
                                'key'       => 'booked-in',
                                'timestamp' => null
                            ]
                        ],
                        'number_pallets'        => 1,
                        'number_boxes'          => 1,
                        'number_oversizes'      => 1,
                        'number_services'       => 2,
                        'number_physical_goods' => 0,
                        'state_label'           => 'In Process',
                        'state_icon'            => [
                            'tooltip' => 'In process',
                            'icon'    => 'fal fa-seedling',
                            'class'   => 'text-lime-500',
                            'color'   => 'lime',
                            'app'     => [
                                'name' => 'seedling',
                                'type' => 'font-awesome-5'
                            ]
                        ],
                        'estimated_delivery_date' => '2024-10-12T00:00:00.000000Z'
                    ]
                ]
                // 'subNavigation' => $subNavigation,
                // 'tabs' => [
                //     'current'    => $this->tab,
                //     'navigation' => CustomerTabsEnum::navigation()

                // ],


            ]
        );
    }


    public function jsonResponse(CustomerClient $customerClient): CustomerClientResource
    {
        return new CustomerClientResource($customerClient);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (CustomerClient $customerClient, array $routeParameters, string $suffix = null) {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Clients')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $customerClient->name,
                        ],

                    ],
                    'suffix' => $suffix

                ],
            ];
        };

        $customerClient = CustomerClient::where('ulid', $routeParameters['customerClient'])->first();


        return match ($routeName) {
            'grp.org.customers.show',
            => array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $customerClient,
                    [
                        'index' => [
                            'name'       => 'grp.org.customers.index',
                            'parameters' => Arr::only($routeParameters, ['organisation'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.customers.customers.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'customer'])
                        ]
                    ],
                    $suffix
                ),
            ),

            'grp.org.shops.show.crm.customers.show.customer-clients.show'
             => array_merge(
                 (new ShowCustomer())->getBreadcrumbs('grp.org.shops.show.crm.customers.show', $routeParameters),
                 $headCrumb(
                     $customerClient,
                     [
                         'index' => [
                             'name'       => 'grp.org.shops.show.crm.customers.show.customer-clients.index',
                             'parameters' => $routeParameters
                         ],
                         'model' => [
                             'name'       => 'grp.org.shops.show.crm.customers.show.customer-clients.show',
                             'parameters' => $routeParameters


                         ]
                     ],
                     $suffix
                 )
             ),
            default => []
        };
    }

    public function getPrevious(CustomerClient $customerClient, ActionRequest $request): ?array
    {
        $previous = CustomerClient::where('ulid', '<', $customerClient->ulid)->orderBy('ulid', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(CustomerClient $customerClient, ActionRequest $request): ?array
    {
        $next = CustomerClient::where('ulid', '>', $customerClient->ulid)->orderBy('ulid')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?CustomerClient $customerClient, string $routeName): ?array
    {
        if (!$customerClient) {
            return null;
        }

        // return match ($routeName) {
        //     'grp.org.shops.show.crm.customers.show.customer-clients.show' => [
        //         'label' => $customerClient->name,
        //         'route' => [
        //             'name'       => $routeName,
        //             'parameters' => [
        //                 'organisation'   => $customerClient->organisation->slug,
        //                 'shop'           => $customerClient->shop->slug,
        //                 'customer'       => $customerClient->customer->slug,
        //                 'customerClient' => $customerClient->ulid,
        //             ]

        //         ]
        //     ]
        // };
        return null;
    }
}
