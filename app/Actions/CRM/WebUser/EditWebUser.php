<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:25:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Actions\InertiaAction;
use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use App\Models\SysAdmin\WebUser;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditWebUser extends InertiaAction
{
    public function handle(WebUser $webUser): WebUser
    {
        return $webUser;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("crm.customers.edit");
    }

    /** @noinspection PhpUnusedParameterInspection */

    public function inCustomerInTenant(Customer $customer, WebUser $webUser, ActionRequest $request): WebUser
    {
        $this->initialisation($request);

        return $this->handle($webUser);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inCustomerInShop(Shop $shop, Customer $customer, WebUser $webUser, ActionRequest $request): WebUser
    {
        $this->initialisation($request);

        return $this->handle($webUser);
    }


    public function htmlResponse(WebUser $webUser, ActionRequest $request): Response
    {
        $scope     = match ($request->route()->getName()) {
            'grp.crm.customers.show.web-users.edit' => $request->route()->parameters()['customer'],
            default                                 => app('currentTenant')
        };
        $container = null;
        if (class_basename($scope) == 'Customer') {
            $container = [
                'icon'    => ['fal', 'fa-user'],
                'tooltip' => __('Customer'),
                'label'   => Str::possessive($scope->name)
            ];
        }

        return Inertia::render(
            'EditModel',
            [
                'title'       => __('web user'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
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
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ]
                    ],
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'   => __('credentials'),
                            'icon'    => 'fa-light fa-key',
                            'current' => true,
                            'fields'  => [
                                'username' => [
                                    'type'  => 'input',
                                    'label' => __('username'),
                                    'value' => $webUser->username
                                ],
                                'password' => [
                                    'type'  => 'password',
                                    'label' => __('password'),
                                    'value' => ''
                                ],

                            ]
                        ],


                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.web-user.update',
                            'parameters' => [$webUser->slug]

                        ],
                    ]
                ]
            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowWebUser::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('editing').')'
        );
    }
}
