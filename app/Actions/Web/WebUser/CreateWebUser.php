<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 03:11:37 Greenwich Mean Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebUser;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Enums\Web\WebUser\WebUserTypeEnum;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Tenancy\Tenant;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateWebUser extends InertiaAction
{
    private Customer|Website|Tenant $parent;

    private ?Shop $shop = null;


    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.customers.view')
            );
    }


    public function htmlResponse(): Response
    {
        return Inertia::render(
            'Web/StoreWebUser',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->shop),
                'title'       => '+ '.__('web user'),
                'pageHead'    => [
                    'title' => __('web user'),
                ],

                'fields' => [
                    'type'     => [
                        'options' => [
                            WebUserTypeEnum::WEB->value => [
                                'label' => __('Customer')
                            ],
                            WebUserTypeEnum::API->value => [
                                'label' => __('API user')
                            ]
                        ]
                    ],
                    'email'    => [
                        'label' => __('email')
                    ],
                    'password' => [
                        'label' => __('password')
                    ]
                ],
                'parent' => $this->parent
            ]
        );
    }


    public function asController()
    {
        $this->parent = app('currentTenant');
        $this->validateAttributes();
    }

    public function inShopInCustomer(Shop $shop, Customer $customer)
    {
        $this->parent = $customer;
        $this->shop   = $shop;
        $this->validateAttributes();
    }

    public function inCustomer(Customer $customer)
    {
        $this->parent = $customer;
        $this->shop   = $customer->shop;
        $this->validateAttributes();
    }

    public function getBreadcrumbs(string $routeName, Shop $shop): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('web users')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'customers.index'            => $headCrumb(),
            'shops.show.customers.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($shop),
                $headCrumb([$shop->id])
            ),
            default => []
        };
    }
}
