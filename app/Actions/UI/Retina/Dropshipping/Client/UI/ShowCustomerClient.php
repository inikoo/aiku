<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 16 Oct 2024 10:47:26 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Dropshipping\Client\UI;

use App\Actions\RetinaAction;
use App\Enums\UI\CRM\CustomerClientTabsEnum;
use App\Enums\UI\CRM\CustomerTabsEnum;
use App\Http\Resources\CRM\CustomerClientResource;
use App\Models\Dropshipping\CustomerClient;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowCustomerClient extends RetinaAction
{
    // use WithActionButtons;
    // use WithWebUserMeta;

    public function handle(CustomerClient $customerClient): CustomerClient
    {
        return $customerClient;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->is_root;
    }


    public function asController(
        CustomerClient $customerClient,
        ActionRequest $request
    ): CustomerClient {
        $this->initialisation($request)->withTab(CustomerTabsEnum::values());

        return $this->handle($customerClient);
    }


    public function htmlResponse(CustomerClient $customerClient, ActionRequest $request): Response
    {

        // $shopMeta = [];

        // if ($request->route()->getName() == 'customers.show') {
        //     $shopMeta = [
        //         'href'     => ['shops.show', $customerClient->customer->shop->slug],
        //         'name'     => $customerClient->customer->shop->code,
        //         'leftIcon' => [
        //             'icon'    => 'fal fa-store-alt',
        //             'tooltip' => __('Shop'),
        //         ],
        //     ];
        // }

        return Inertia::render(
            'Dropshipping/Client/CustomerClient',
            [
                'title'       => __('customer client'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $customerClient,
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                // 'navigation' => [
                //     'previous' => $this->getPrevious($customerClient, $request),
                //     'next'     => $this->getNext($customerClient, $request),
                // ],
                'pageHead' => [
                    'title'     => $customerClient->name,
                    'model'     => __('Client'),
                    'icon'      => [
                        'icon'  => ['fal', 'fa-folder'],
                        'title' => __('customer client')
                    ],
                    // 'actions' => [
                    //     $this->canDelete ? $this->getDeleteActionIcon($request) : null,
                    //     $this->canEdit ? $this->getEditActionIcon($request) : null,
                    //     [
                    //         'type'    => 'button',
                    //         'style'   => 'create',
                    //         'label'   => 'Add order',
                    //         'key'     => 'add_order',
                    //         'route'   => [
                    //             'name'       => 'grp.models.pallet-delivery.multiple-pallets.store',
                    //             'parameters' => [
                    //                 'palletDelivery' => 3
                    //             ]
                    //         ]
                    //     ],
                    // ],
                    // 'subNavigation' => $subNavigation,
                ],
                'tabs'          => [
                    'current'    => $this->tab,
                    'navigation' => CustomerClientTabsEnum::navigation()

                ],

                // CustomerTabsEnum::SHOWCASE->value => $this->tab == CustomerTabsEnum::SHOWCASE->value ?
                //     fn () => GetCustomerClientShowcase::run($customerClient)
                //     : Inertia::lazy(fn () => GetCustomerClientShowcase::run($customerClient)),

                // CustomerTabsEnum::ORDERS->value => $this->tab == CustomerTabsEnum::ORDERS->value ?
                //     fn () => OrderResource::collection(IndexOrders::run($customer))
                //     : Inertia::lazy(fn () => OrderResource::collection(IndexOrders::run($customer))),

                /*
                CustomerTabsEnum::PRODUCTS->value => $this->tab == CustomerTabsEnum::PRODUCTS->value ?
                    fn () => ProductsResource::collection(IndexDropshippingRetinaProducts::run($customer))
                    : Inertia::lazy(fn () => ProductsResource::collection(IndexDropshippingRetinaProducts::run($customer))),
                */

                // CustomerTabsEnum::DISPATCHED_EMAILS->value => $this->tab == CustomerTabsEnum::DISPATCHED_EMAILS->value ?
                //     fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($customer))
                //     : Inertia::lazy(fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($customer))),
                // CustomerTabsEnum::WEB_USERS->value => $this->tab == CustomerTabsEnum::WEB_USERS->value ?
                //     fn () => WebUsersResource::collection(IndexWebUsers::run($customer))
                //     : Inertia::lazy(
                //         fn () => WebUsersResource::collection(IndexWebUsers::run($customer))
                //     ),

            ]
        );
        // ->table(IndexOrders::make()->tableStructure($customer))
        //     //    ->table(IndexDropshippingRetinaProducts::make()->tableStructure($customer))
        //     ->table(IndexDispatchedEmails::make()->tableStructure($customer))
        //     ->table(
        //         IndexWebUsers::make()->tableStructure(
        //             parent: $customer,
        //             modelOperations: [
        //                 'createLink' => [
        //                     [
        //                         'type'    => 'button',
        //                         'style'   => 'create',
        //                         'tooltip' => __('Create new web user'),
        //                         'label'   => __('Create Web User'),
        //                         'route'   => [
        //                             'method'     => 'get',
        //                             'name'       => 'grp.org.fulfilments.show.crm.customers.show.web-users.create',
        //                             'parameters' => [
        //                                 $customer->organisation->slug,
        //                                 $customer->shop->slug,
        //                                 $customer->slug
        //                             ]
        //                         ]
        //                     ]
        //                 ]
        //             ],
        //             prefix: CustomerTabsEnum::WEB_USERS->value,
        //             canEdit: $this->canEdit
        //         )
        //     );
    }


    public function jsonResponse(CustomerClient $customerClient): CustomerClientResource
    {
        return new CustomerClientResource($customerClient);
    }

    public function getBreadcrumbs(CustomerClient $customerClient, $routeName, $routeParameters): array
    {
        return
            array_merge(
                IndexCustomerClients::make()->getBreadcrumbs($routeName, $routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'retina.dropshipping.client.show',
                                'parameters' => $routeParameters
                            ],
                            'label' => $customerClient->name,
                        ]
                    ]
                ]
            );
    }

    // public function getPrevious(CustomerClient $customerClient, ActionRequest $request): ?array
    // {
    //     $previous = CustomerClient::where('ulid', '<', $customerClient->ulid)->orderBy('ulid', 'desc')->first();

    //     return $this->getNavigation($previous, $request->route()->getName());
    // }

    // public function getNext(CustomerClient $customerClient, ActionRequest $request): ?array
    // {
    //     $next = CustomerClient::where('ulid', '>', $customerClient->ulid)->orderBy('ulid')->first();

    //     return $this->getNavigation($next, $request->route()->getName());
    // }

    // private function getNavigation(?CustomerClient $customerClient, string $routeName): ?array
    // {
    //     if (!$customerClient) {
    //         return null;
    //     }

    //     return match ($routeName) {
    //         'grp.org.shops.show.crm.customers.show.customer-clients.show' => [
    //             'label' => $customerClient->name,
    //             'route' => [
    //                 'name'       => $routeName,
    //                 'parameters' => [
    //                     'organisation'   => $customerClient->organisation->slug,
    //                     'shop'           => $customerClient->shop->slug,
    //                     'customer'       => $customerClient->customer->slug,
    //                     'customerClient' => $customerClient->ulid,
    //                 ]

    //             ]
    //         ]
    //     };
    // }
}
