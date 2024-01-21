<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:25:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Actions\InertiaAction;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Enums\CRM\WebUser\WebUserTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateWebUser extends InertiaAction
{
    private Customer|Website|Organisation $parent;

    private ?Shop $shop = null;


    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo("crm.{$this->shop->id}.view")
            );
    }


    public function htmlResponse(): Response
    {
        return Inertia::render(
            'Web/StoreWebUser',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $this->shop),
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
                            \App\Enums\CRM\WebUser\WebUserTypeEnum::API->value => [
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
