<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 03:11:37 Greenwich Mean Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebUser;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Web\Website;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;


class CreateWebUser extends InertiaAction
{
    private Customer|Website|Tenant $parent;


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
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => '+ '.__('web user'),
                'pageHead'    => [
                    'title' => __('web user'),
                ],

                'fields' => [
                    'type'     => [
                        'options' => [
                            'web' => [
                                'label' => __('Customer')
                            ],
                            'api' => [
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
                'parent'=>$this->parent
            ]
        );
    }


    public function asController(Request $request)
    {
        $this->parent = app('currentTenant');
        $this->validateAttributes();
    }

    public function InShopInCustomer(Shop $shop, Customer $customer)
    {
        $this->parent = $customer;
        $this->validateAttributes();
    }

    public function InCustomer(Customer $customer)
    {
        $this->parent = $customer;
        $this->validateAttributes();
    }

    public function getBreadcrumbs(string $routeName, Customer|Website|Tenant $parent): array
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
            'customers.index' => $headCrumb(),
            'shops.show.customers.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($parent),
                $headCrumb([$parent->id])
            ),
            default => []
        };
    }

}
