<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\UI;

use App\Actions\InertiaAction;
use App\Actions\Mail\DispatchedEmail\IndexDispatchedEmails;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\OMS\Order\UI\IndexOrders;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Enums\UI\CustomerTabsEnum;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowCustomer extends InertiaAction
{
    public function handle(Customer $customer): Customer
    {
        return $customer;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo('crm.customers.edit');
        $this->canDelete = $request->user()->hasPermissionTo('crm.customers.edit');

        return $request->user()->hasPermissionTo("shops.customers.view");
    }

    public function inTenant(Customer $customer, ActionRequest $request): Customer
    {
        $this->initialisation($request)->withTab(CustomerTabsEnum::values());

        return $this->handle($customer);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, Customer $customer, ActionRequest $request): Customer
    {
        $this->initialisation($request)->withTab(CustomerTabsEnum::values());

        return $this->handle($customer);
    }


    public function htmlResponse(Customer $customer, ActionRequest $request): Response
    {
        $webUsersMeta = match ($customer->stats->number_web_users) {
            0 => [
                'name'                  => 'add web user',
                'leftIcon'              => [
                    'icon'    => 'fal fa-globe',
                    'tooltip' => __('Web user')
                ],
                'emptyWithCreateAction' => [
                    'label' => __('web user')
                ]
            ],
            1 => [
                'href' => [
                    $request->route()->getName().'.web-users.show',
                    array_merge_recursive($request->route()->originalParameters(), ['user' => $customer->webUsers->first()])

                ],

                'name'     => $customer->webUsers->first()->slug,
                'leftIcon' => [
                    'icon'    => 'fal fa-globe',
                    'tooltip' => __('Web user'),
                ],

            ],
            default => [
                'name'     => $customer->webUsers->count(),
                'leftIcon' => [
                    'icon'    => 'fal fa-globe',
                    'tooltip' => __('Web users')
                ],
            ]
        };

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
            'CRM/Customer',
            [
                'title'       => __('customer'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($customer, $request),
                    'next'     => $this->getNext($customer, $request),
                ],
                'pageHead'    => [
                    'title'   => $customer->name,
                    'icon'    => [
                        'icon'  => ['fal', 'fa-user'],
                        'title' => __('customer')
                    ],
                    'meta'    => array_filter([
                        $shopMeta,
                        $webUsersMeta
                    ]),
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                        $this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'grp.crm.customers.remove',
                                'parameters' => $request->route()->originalParameters()
                            ]

                        ] : false
                    ]
                ],
                'tabs'        => [
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
                    fn () => ProductResource::collection(IndexProducts::run($customer))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($customer))),
                */

                CustomerTabsEnum::DISPATCHED_EMAILS->value => $this->tab == CustomerTabsEnum::DISPATCHED_EMAILS->value ?
                    fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($customer))
                    : Inertia::lazy(fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($customer))),

            ]
        )->table(IndexOrders::make()->tableStructure($customer))
            //    ->table(IndexProducts::make()->tableStructure($customer))
            ->table(IndexDispatchedEmails::make()->tableStructure($customer));
    }


    public function jsonResponse(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
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
                    'suffix'         => $suffix

                ],
            ];
        };

        return match ($routeName) {
            'grp.crm.customers.show',
            'grp.crm.customers.edit'
            => array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['customer'],
                    [
                        'index' => [
                            'name'       => 'grp.crm.customers.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'grp.crm.customers.show',
                            'parameters' => [$routeParameters['customer']]
                        ]
                    ],
                    $suffix
                ),
            ),

            'grp.crm.shops.show.customers.show',
            'grp.crm.shops.show.customers.edit'
            => array_merge(
                (new ShowShop())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $routeParameters['customer'],
                    [
                        'index' => [
                            'name'       => 'grp.crm.shops.show.customers.index',
                            'parameters' => [
                                $routeParameters['shop']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'grp.crm.shops.show.customers.show',
                            'parameters' => [
                                $routeParameters['shop']->slug,
                                $routeParameters['customer']->slug
                            ]
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
        $previous = Customer::where('slug', '<', $customer->slug)->when(true, function ($query) use ($customer, $request) {
            if ($request->route()->getName() == 'shops.show.customers.show') {
                $query->where('customers.shop_id', $customer->shop_id);
            }
        })->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Customer $customer, ActionRequest $request): ?array
    {
        $next = Customer::where('slug', '>', $customer->slug)->when(true, function ($query) use ($customer, $request) {
            if ($request->route()->getName() == 'shops.show.customers.show') {
                $query->where('customers.shop_id', $customer->shop_id);
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
            'grp.crm.customers.show' => [
                'label' => $customer->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'customer' => $customer->slug
                    ]

                ]
            ],
            'grp.crm.shops.show.customers.show' => [
                'label' => $customer->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'shop'     => $customer->shop->slug,
                        'customer' => $customer->slug
                    ]

                ]
            ]
        };
    }
}
