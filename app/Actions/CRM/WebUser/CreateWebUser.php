<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:25:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Enums\CRM\WebUser\WebUserTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateWebUser extends OrgAction
{
    private Customer|FulfilmentCustomer|Website|Organisation $parent;


    public function authorize(ActionRequest $request): bool
    {

        if($this->parent instanceof FulfilmentCustomer) {
            return  $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        }

        return false;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
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




    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): ActionRequest
    {
        $this->parent=$fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $request;
    }

    public function asController(Organisation $organisation, Shop $shop, Customer $customer, ActionRequest $request): ActionRequest
    {
        $this->parent=$customer;
        $this->initialisationFromShop($shop, $request);
        return $request;
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
