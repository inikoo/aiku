<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\UI;

use App\Actions\CRM\WebUser\IndexWebUsers;
use App\Actions\Mail\DispatchedEmail\IndexDispatchedEmails;
use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OMS\Order\UI\IndexOrders;
use App\Actions\OrgAction;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Actions\Traits\WithWebUserMeta;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Enums\UI\CustomerTabsEnum;
use App\Http\Resources\CRM\CustomersResource;
use App\Http\Resources\CRM\WebUsersResource;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\CRM\Customer;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowCustomer extends OrgAction
{
    use WithActionButtons;
    use WithWebUserMeta;

    private Organisation|Shop $parent;

    public function handle(Customer $customer): Customer
    {
        return $customer;
    }


    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Organisation) {
            $this->canEdit   = $request->user()->hasPermissionTo("shops.{$this->organisation->id}.edit");
            $this->canDelete = $request->user()->hasPermissionTo("shops.{$this->organisation->id}.edit");

            return $request->user()->hasPermissionTo("shops.{$this->organisation->id}.view");
        }
        if ($this->parent instanceof Shop) {
            $this->canEdit   = $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
            $this->canDelete = $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");

            return $request->user()->hasPermissionTo("crm.{$this->shop->id}.view");
        }

        return false;
    }

    public function inOrganisation(Organisation $organisation, Customer $customer, ActionRequest $request): Customer
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(CustomerTabsEnum::values());

        return $this->handle($customer);
    }


    public function asController(
        Organisation $organisation,
        Shop $shop,
        Customer $customer,
        ActionRequest $request
    ): Customer {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(CustomerTabsEnum::values());

        return $this->handle($customer);
    }


    public function htmlResponse(Customer $customer, ActionRequest $request): Response
    {
        $webUsersMeta = $this->getWebUserMeta($customer, $request);

        $shopMeta = [];

        if ($request->route()->getName() == 'customers.show') {
            $shopMeta = [
                'href'     => ['shops.show', $customer->shop->slug],
                'name'     => $customer->shop->code,
                'leftIcon' => [
                    'icon'    => 'fal fa-store-alt',
                    'tooltip' => __('Shop'),
                ],
            ];
        }


        return Inertia::render(
            'Org/Shop/CRM/Customer',
            [
                'title'       => __('customer'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation' => [
                    'previous' => $this->getPrevious($customer, $request),
                    'next'     => $this->getNext($customer, $request),
                ],
                'pageHead' => [
                    'title' => $customer->name,
                    'icon'  => [
                        'icon'  => ['fal', 'fa-user'],
                        'title' => __('customer')
                    ],
                    'meta' => array_filter([
                        $shopMeta,
                        $webUsersMeta
                    ]),
                    'actions' => [
                        $this->canDelete ? $this->getDeleteActionIcon($request) : null,
                        $this->canEdit ? $this->getEditActionIcon($request) : null,
                    ],
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => CustomerTabsEnum::navigation()

                ],

                CustomerTabsEnum::SHOWCASE->value => $this->tab == CustomerTabsEnum::SHOWCASE->value ?
                    fn () => GetCustomerShowcase::run($customer)
                    : Inertia::lazy(fn () => GetCustomerShowcase::run($customer)),

                CustomerTabsEnum::ORDERS->value => $this->tab == CustomerTabsEnum::ORDERS->value ?
                    fn () => OrderResource::collection(IndexOrders::run($customer))
                    : Inertia::lazy(fn () => OrderResource::collection(IndexOrders::run($customer))),

                /*
                CustomerTabsEnum::PRODUCTS->value => $this->tab == CustomerTabsEnum::PRODUCTS->value ?
                    fn () => ProductsResource::collection(IndexProducts::run($customer))
                    : Inertia::lazy(fn () => ProductsResource::collection(IndexProducts::run($customer))),
                */

                CustomerTabsEnum::DISPATCHED_EMAILS->value => $this->tab == CustomerTabsEnum::DISPATCHED_EMAILS->value ?
                    fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($customer))
                    : Inertia::lazy(fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($customer))),
                CustomerTabsEnum::WEB_USERS->value => $this->tab == CustomerTabsEnum::WEB_USERS->value ?
                    fn () => WebUsersResource::collection(IndexWebUsers::run($customer))
                    : Inertia::lazy(
                        fn () => WebUsersResource::collection(IndexWebUsers::run($customer))
                    ),

            ]
        )->table(IndexOrders::make()->tableStructure($customer))
            //    ->table(IndexProducts::make()->tableStructure($customer))
            ->table(IndexDispatchedEmails::make()->tableStructure($customer))
            ->table(
                IndexWebUsers::make()->tableStructure(
                    parent: $customer,
                    modelOperations: [
                        'createLink' => [
                            [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('Create new web user'),
                                'label'   => __('Create Web User'),
                                'route'   => [
                                    'method'     => 'get',
                                    'name'       => 'grp.org.fulfilments.show.crm.customers.show.web-users.create',
                                    'parameters' => [
                                        $customer->organisation->slug,
                                        $customer->shop->slug,
                                        $customer->slug
                                    ]
                                ]
                            ]
                        ]
                    ],
                    prefix: CustomerTabsEnum::WEB_USERS->value,
                    canEdit: $this->canEdit
                )
            );
    }


    public function jsonResponse(Customer $customer): CustomersResource
    {
        return new CustomersResource($customer);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (Customer $customer, array $routeParameters, string $suffix = null) {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('customers')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $customer->name,
                        ],

                    ],
                    'suffix' => $suffix

                ],
            ];
        };

        $customer = Customer::where('slug', $routeParameters['customer'])->first();


        return match ($routeName) {
            'grp.org.customers.show',
            => array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $customer,
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

            'grp.org.shops.show.crm.customers.show',
            'grp.org.shops.show.crm.customers.edit'
            => array_merge(
                ShowShop::make()->getBreadcrumbs(Arr::only($routeParameters, ['organisation', 'shop'])),
                $headCrumb(
                    $customer,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.crm.customers.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'shop'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.crm.customers.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'shop', 'customer'])
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(Customer $customer, ActionRequest $request): ?array
    {
        $previous = Customer::where('slug', '<', $customer->slug)->when(
            true,
            function ($query) use ($customer, $request) {
                if ($request->route()->getName() == 'shops.show.customers.show') {
                    $query->where('customers.shop_id', $customer->shop_id);
                }
            }
        )->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Customer $customer, ActionRequest $request): ?array
    {
        $next = Customer::where('slug', '>', $customer->slug)->when(true, function ($query) use ($customer, $request) {
            if ($this->parent instanceof Organisation) {
                $query->where('customers.organisation_id', $this->parent->id);
            } elseif ($this->parent instanceof Shop) {
                $query->where('customers.shop_id', $this->parent->id);
            }
        })->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Customer $customer, string $routeName): ?array
    {
        if (!$customer) {
            return null;
        }

        return match ($routeName) {
            'grp.org.customers.show' => [
                'label' => $customer->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $customer->organisation->slug,
                        'customer'     => $customer->slug
                    ]

                ]
            ],
            'grp.org.shops.show.crm.customers.show' => [
                'label' => $customer->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $customer->organisation->slug,
                        'shop'         => $customer->shop->slug,
                        'customer'     => $customer->slug
                    ]

                ]
            ]
        };
    }
}
