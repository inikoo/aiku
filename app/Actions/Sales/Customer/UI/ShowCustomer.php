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
use App\Actions\Marketing\Shop\ShowShop;
use App\Actions\Sales\Order\IndexOrders;
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

    public function asController(Customer $customer, ActionRequest $request): Customer
    {
        $this->initialisation($request)->withTab(CustomerTabsEnum::values());

        return $this->handle($customer);
    }

    public function inShop(Shop $shop, Customer $customer, ActionRequest $request): Customer
    {
        $this->initialisation($request);

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


    public function htmlResponse(Customer $customer): Response
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
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $customer),
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
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => CustomerTabsEnum::navigation()

                ],

                CustomerTabsEnum::ORDERS->value => $this->tab == CustomerTabsEnum::ORDERS->value ?
                    fn () => OrderResource::collection(IndexOrders::run($this->customer))
                    : Inertia::lazy(fn () => OrderResource::collection(IndexOrders::run($this->customer))),

                CustomerTabsEnum::PRODUCTS->value => $this->tab == CustomerTabsEnum::PRODUCTS->value ?
                    fn () => ProductResource::collection(IndexProducts::run($this->customer))
                    : Inertia::lazy(fn () => ProductResource::collection(IndexProducts::run($this->customer))),

                CustomerTabsEnum::DISPATCHED_EMAILS->value => $this->tab == CustomerTabsEnum::DISPATCHED_EMAILS->value ?
                    fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($this->customer))
                    : Inertia::lazy(fn () => DispatchedEmailResource::collection(IndexDispatchedEmails::run($this->customer))),

            ]
        )->table(IndexOrders::make()->tableStructure($customer))
         ->table(IndexProducts::make()->tableStructure($customer))
         ->table(IndexDispatchedEmails::make()->tableStructure($customer));
    }


    public function jsonResponse(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }

    public function getBreadcrumbs(string $routeName, Customer $customer): array
    {
        $headCrumb = function (array $routeParameters = []) use ($customer, $routeName) {
            $indexRouteParameters = $routeParameters;
            array_pop($indexRouteParameters);

            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'name'            => $customer->reference,
                    'index'           => [
                        'route'           => preg_replace('/(show|edit)$/', 'index', $routeName),
                        'routeParameters' => $indexRouteParameters,
                        'overlay'         => __('customers list')
                    ],
                    'modelLabel'      => [
                        'label' => __('customer')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'customers.show', 'customers.edit' => $headCrumb([$customer->shop->slug]),
            'shops.show.customers.show',
            'shops.show.customers.edit'
            => array_merge(
                (new ShowShop())->getBreadcrumbs($customer->shop),
                $headCrumb([$customer->shop->slug, $customer->slug])
            ),
            default => []
        };
    }
}
