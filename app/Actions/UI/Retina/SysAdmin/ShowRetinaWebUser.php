<?php
/*
 * author Arya Permana - Kirin
 * created on 09-01-2025-09h-25m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\UI\Retina\SysAdmin;

use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\RetinaAction;
use App\Http\Resources\CRM\WebUserResource;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowRetinaWebUser extends RetinaAction
{
    public function handle(WebUser $webUser): WebUser
    {
        return $webUser;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->is_root;
    }


    public function asController(WebUser $webUser, ActionRequest $request): WebUser
    {
        $this->initialisation($request);

        return $this->handle($webUser);
    }

    public function htmlResponse(WebUser $webUser, ActionRequest $request): Response
    {
        $model = __('web user');
        $iconRight  = [];
        $subNavigation = [];

        return Inertia::render(
            'SysAdmin/RetinaWebUser',
            [
                'title'       => __('Web user'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'model'         => $model,
                    'title'         => $webUser->username,
                    'subNavigation' => $subNavigation,
                    'icon'              => 'fal fa-user',
                    'iconRight' => $iconRight,
                // 'meta'          => [
                    //     [
                    //         'name' => $webUser->username
                    //     ]
                    // ],
                    'actions'   => [ [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ],

                    ],

                ],
                'data'     => new WebUserResource($webUser)
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
                            'label' => __('Web users')
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

        $webUser = WebUser::where('slug', $routeParameters['webUser'])->first();

        return match ($routeName) {
            'retina.sysadmin.web-users.show' =>
            array_merge(
                ShowRetinaSysAdminDashboard::make()->getBreadcrumbs(
                    $routeName
                ),
                $headCrumb(
                    $webUser,
                    [
                        'index' => [
                            'name'       => 'retina.sysadmin.web-users.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'retina.sysadmin.web-users.show',
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
