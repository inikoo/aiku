<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Dropshipping\Client;

use App\Actions\OrgAction;
use App\Actions\RetinaAction;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Actions\Traits\WithWebUserMeta;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Enums\UI\CRM\CustomerClientTabsEnum;
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
                // 'breadcrumbs' => $this->getBreadcrumbs(
                //     $request->route()->getName(),
                //     $request->route()->originalParameters()
                // ),
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

    // public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    // {
    //     $headCrumb = function (CustomerClient $customerClient, array $routeParameters, string $suffix = null) {
    //         return [
    //             [

    //                 'type'           => 'modelWithIndex',
    //                 'modelWithIndex' => [
    //                     'index' => [
    //                         'route' => $routeParameters['index'],
    //                         'label' => __('Clients')
    //                     ],
    //                     'model' => [
    //                         'route' => $routeParameters['model'],
    //                         'label' => $customerClient->name,
    //                     ],

    //                 ],
    //                 'suffix' => $suffix

    //             ],
    //         ];
    //     };

    //     $customerClient = CustomerClient::where('ulid', $routeParameters['customerClient'])->first();


    //     return match ($routeName) {
    //         'grp.org.customers.show',
    //         => array_merge(
    //             ShowDashboard::make()->getBreadcrumbs(),
    //             $headCrumb(
    //                 $customerClient,
    //                 [
    //                     'index' => [
    //                         'name'       => 'grp.org.customers.index',
    //                         'parameters' => Arr::only($routeParameters, ['organisation'])
    //                     ],
    //                     'model' => [
    //                         'name'       => 'grp.org.customers.customers.show',
    //                         'parameters' => Arr::only($routeParameters, ['organisation', 'customer'])
    //                     ]
    //                 ],
    //                 $suffix
    //             ),
    //         ),

    //         'grp.org.shops.show.crm.customers.show.customer-clients.show'
    //          => array_merge(
    //              (new ShowCustomer())->getBreadcrumbs('grp.org.shops.show.crm.customers.show', $routeParameters),
    //              $headCrumb(
    //                  $customerClient,
    //                  [
    //                      'index' => [
    //                          'name'       => 'grp.org.shops.show.crm.customers.show.customer-clients.index',
    //                          'parameters' => $routeParameters
    //                      ],
    //                      'model' => [
    //                          'name'       => 'grp.org.shops.show.crm.customers.show.customer-clients.show',
    //                          'parameters' => $routeParameters


    //                      ]
    //                  ],
    //                  $suffix
    //              )
    //          ),
    //         default => []
    //     };
    // }

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