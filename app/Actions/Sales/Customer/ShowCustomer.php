<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Http\Resources\Sales\CustomerResource;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;


class ShowCustomer extends InertiaAction
{


    public function handle(Customer $customer): Customer
    {
        return $customer;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.customers.view");
    }

    public function asController(Customer $customer, Request $request): Customer
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($customer);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function InShop(Shop $shop, Customer $customer, Request $request): Customer
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($customer);
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
                'name'     => $customer->webUsers->first()->username,
                'leftIcon' => [
                    'icon'    => 'fal fa-globe',
                    'tooltip' => __('Web user')
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




        return Inertia::render(
            'Sales/Customer',
            [
                'title'       => __('customer'),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $customer),
                'pageHead'    => [
                    'title' => $customer->name,
                    'meta'  => [
                        $webUsersMeta
                    ]

                ],
                'customer'    => new CustomerResource($customer)
            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(Customer $customer): CustomerResource
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
                        'route'           => preg_replace('/show$/', 'index', $routeName),
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
            'customers.show' => $headCrumb([$customer->shop_id]),
            'shops.show.customers.show' => array_merge(
                (new ShowShop())->getBreadcrumbs($customer->shop),
                $headCrumb([$customer->shop_id, $customer->id])
            ),
            default => []
        };
    }

}
