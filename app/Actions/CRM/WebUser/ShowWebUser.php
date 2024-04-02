<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:25:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\OrgAction;
use App\Http\Resources\CRM\WebUserResource;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowWebUser extends OrgAction
{
    use WithAuthorizeWebUserScope;

    private FulfilmentCustomer|Customer $parent;

    public function handle(WebUser $webUser): WebUser
    {
        return $webUser;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $this->authorizeWebUserScope($request);
    }



    public function asController(Organisation $organisation, Shop $shop, Customer $customer, WebUser $webUser, ActionRequest $request): WebUser
    {
        $this->parent=$customer;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($webUser);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, WebUser $webUser, ActionRequest $request): WebUser
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($webUser);
    }


    public function htmlResponse(WebUser $webUser, ActionRequest $request): Response
    {
        $container = null;
        if (class_basename($this->parent) == 'Customer') {
            $container = [
                'icon'    => ['fal', 'fa-user'],
                'tooltip' => __('Customer'),
                'label'   => Str::possessive($this->parent->name)
            ];
        } elseif (class_basename($this->parent) == 'FulfilmentCustomer') {
            $container = [
                'icon'    => ['fal', 'fa-user'],
                'tooltip' => __('Customer'),
                'label'   => Str::possessive($this->parent->customer->name)
            ];
        }


        return Inertia::render(
            'Org/Shop/CRM/WebUser',
            [
                'title'       => __('Web user'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'title'     => __('web user'),
                    'container' => $container,
                    'meta'      => [
                        [
                            'name' => $webUser->username
                        ]
                    ],
                    'actions'   => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,

                    ],

                ],
                'webUser'     => new WebUserResource($webUser)
            ]
        );
    }


    public function jsonResponse(WebUser $webUser): WebUserResource
    {
        return new WebUserResource($webUser);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (WebUser $webUser, array $routeParameters, string $suffix) {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('web users')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $webUser->username,
                        ],

                    ],
                    'suffix'         => $suffix

                ],
            ];
        };

        $webUser=WebUser::where('slug', $routeParameters['webUser'])->first();

        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show.web-users.show' =>
            array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs(
                    Arr::except($routeParameters, 'webUser')
                ),
                $headCrumb(
                    $webUser,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.web-users.index',
                            'parameters' => Arr::except($routeParameters, 'webUser')
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.web-users.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                ),
            ),

            'grp.org.shops.show.crm.customers.show.web-users.show' =>
            array_merge(
                ShowCustomer::make()->getBreadcrumbs(
                    'grp.org.shops.show.crm.customers.show',
                    Arr::except($routeParameters, 'webUser')
                ),
                $headCrumb(
                    $webUser,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.crm.customers.show.web-users.index',
                            'parameters' => Arr::except($routeParameters, 'webUser')
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.crm.customers.show.web-users.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                ),
            ),



            default => []
        };
    }


}
