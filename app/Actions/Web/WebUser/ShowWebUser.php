<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 23 Nov 2022 07:45:29 Malaysia Time, KLIA, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Web\WebUser;

use App\Actions\InertiaAction;
use App\Actions\Sales\Customer\UI\ShowCustomer;
use App\Http\Resources\Web\WebUserResource;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Web\WebUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

class ShowWebUser extends InertiaAction
{
    public function handle(WebUser $webUser): WebUser
    {
        return $webUser;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.customers.view");
    }

    public function asController(WebUser $webUser, Request $request): WebUser
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($webUser);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShopInCustomer(Shop $shop, Customer $customer, WebUser $webUser, Request $request): WebUser
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($webUser);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inCustomer(Customer $customer, WebUser $webUser, Request $request): WebUser
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($webUser);
    }

    /*
    private function makeRoute($suffix='',$parameters=[]): array
    {

        $route=$this->routeName;
        $routeParameters=[];
        if($this->routeName=='customers.show'){



        }

        return [$route,$routeParameters];

    }
*/

    public function htmlResponse(WebUser $webUser): Response
    {
        return Inertia::render(
            'Web/WebUser',
            [
                'title'       => __('Web user'),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $webUser),
                'pageHead'    => [
                    'title' => $webUser->slug,
                    'meta'  => [

                    ]

                ],
                'webUser'     => new WebUserResource($webUser)
            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
    }

    #[Pure] public function jsonResponse(WebUser $webUser): WebUserResource
    {
        return new WebUserResource($webUser);
    }


    public function getBreadcrumbs(string $routeName, WebUser $webUser): array
    {
        $headCrumb = function (array $routeParameters = []) use ($webUser, $routeName) {
            $indexRouteParameters = $routeParameters;
            array_pop($indexRouteParameters);

            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'name'            => $webUser->slug,
                    'index'           => [
                        'route'           => preg_replace('/show$/', 'index', $routeName),
                        'routeParameters' => $indexRouteParameters,
                        'overlay'         => __('web users list')
                    ],
                    'modelLabel'      => [
                        'label' => __('web user')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'customers.show' => $headCrumb([$webUser->shop->slug]),

            'shops.show.customers.show.web-users.show' => array_merge(
                (new ShowCustomer())->getBreadcrumbs('shops.show.customers.show', $webUser->customer),
                $headCrumb([$webUser->customer->shop->slug, $webUser->customer->slug, $webUser->slug])
            ),
            default => []
        };
    }
}
