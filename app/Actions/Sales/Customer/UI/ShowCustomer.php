<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer\UI;

use App\Actions\InertiaAction;
use App\Actions\Mail\DispatchedEmail\IndexDispatchedEmails;
use App\Actions\Marketing\Product\UI\IndexProducts;
use App\Actions\Marketing\Shop\UI\ShowShop;
use App\Actions\Sales\Order\IndexOrders;
use App\Actions\UI\Dashboard\Dashboard;
use App\Enums\UI\CustomerTabsEnum;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Http\Resources\Marketing\ProductResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Customer $customer
 */
class ShowCustomer extends InertiaAction
{
    public function handle(Customer $customer): Customer
    {
        return $customer;
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.customers.edit');

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


    private function makeRoute(Customer $customer, $suffix = '', $parameters = []): array
    {
        $route = $this->routeName;
        if ($this->routeName == 'shops.show.customers.show') {
            $routeParameters = [
                $customer->shop->slug,
                $customer->slug
            ];
        } else {
            $routeParameters = [
                $customer->slug
            ];
        }

        $route           .= $suffix;
        $routeParameters = array_merge_recursive($routeParameters, $parameters);


        return [$route, $routeParameters];
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
                'href'     => $this->makeRoute($customer, '.web-users.show', [$customer->webUsers->first()->slug]),
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

        if ($this->routeName == 'customers.show') {
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
            'Sales/Customer',
            [
                'title'       => __('customer'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'title' => $customer->name,
                    'meta'  => array_filter([
                        $shopMeta,
                        $webUsersMeta
                    ]),
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,

                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => CustomerTabsEnum::navigation()

                ],

                CustomerTabsEnum::ORDERS->value => $this->tab == CustomerTabsEnum::ORDERS->value ?
                    fn () => OrderResource::collection(IndexOrders::run($customer))
                    : Inertia::lazy(fn () => OrderResource::collection(IndexOrders::run($customer))),

                CustomerTabsEnum::PRODUCTS->value => $this->tab == CustomerTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(IndexProducts::run($customer))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($customer))),

                CustomerTabsEnum::DISPATCHED_EMAILS->value => $this->tab == CustomerTabsEnum::DISPATCHED_EMAILS->value ?
                    fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($customer))
                    : Inertia::lazy(fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($customer))),

            ]
        )->table(IndexOrders::make()->tableStructure($customer))
            ->table(IndexProducts::make()->tableStructure($customer))
            ->table(IndexDispatchedEmails::make()->tableStructure($customer));
    }


    public function jsonResponse(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (Customer $customer, array $routeParameters, string $suffix) {
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
                            'label' => $customer->reference,
                        ],

                    ],
                    'suffix'=> $suffix

                ],
            ];
        };

        return match ($routeName) {
            'customers.show',
            'customers.edit' =>

            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $routeParameters['customer'],
                    [
                        'index' => [
                            'name'       => 'customers.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'customers.show',
                            'parameters' => [$routeParameters['customer']->slug]
                        ]
                    ],
                    $suffix
                ),
            ),


            'shops.show.customers.show',
            'shops.show.customers.edit'
            => array_merge(
                (new ShowShop())->getBreadcrumbs($routeParameters['shop']),
                $headCrumb(
                    $routeParameters['customer'],
                    [
                        'index' => [
                            'name'       => 'shops.show.customers.index',
                            'parameters' => [
                                $routeParameters['shop']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'shops.show.customers.show',
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
}
