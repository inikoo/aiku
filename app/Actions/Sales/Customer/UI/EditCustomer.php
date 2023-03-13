<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Sales\CustomerResource;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

class EditCustomer extends InertiaAction
{
    use HasUICustomer;
    private bool $can_edit;

    public function handle(Customer $customer): Customer
    {
        return $customer;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.customers.edit");
    }

    public function asController(Customer $customer, ActionRequest $request): Customer
    {
        $this->initialisation($request);

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
                    'exitEdit'  => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters),
                        ]


                    ],

                ],
                'customer'    => new CustomerResource($customer)
            ]
        );
    }


    #[Pure] public function jsonResponse(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }
}
