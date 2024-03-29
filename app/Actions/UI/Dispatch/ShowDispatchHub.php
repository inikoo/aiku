<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dispatch;

use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDispatchHub
{
    use AsAction;
    use WithInertia;

    public function handle($scope)
    {
        return $scope;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("oms.view");
    }

    public function inOrganisation(): Organisation
    {
        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop): Shop
    {
        return $this->handle($shop);
    }


    public function htmlResponse(Organisation|Shop $scope, ActionRequest $request): Response
    {
        $container = null;
        //$scopeType = 'Organisation';
        if (class_basename($scope) == 'Shop') {
            //$scopeType = 'Shop';
            $container = [
                'icon'    => ['fal', 'fa-store-alt'],
                'tooltip' => __('Shop'),
                'label'   => Str::possessive($scope->name)
            ];
        }



        return Inertia::render(
            'Dispatch/DispatchHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => 'dispatch',
                'pageHead'    => [
                    'title'     => __('Dispatch'),
                    'container' => $container
                ],


            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return match ($routeName) {
            'shops.show.dispatch.hub' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'shops.show.dispatch.hub',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('dispatch'),
                        ]
                    ]
                ]
            ),
            default =>
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'grp.dispatch.hub'
                            ],
                            'label' => __('dispatch'),
                        ]
                    ]
                ]
            )
        };
    }

}
