<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:40:27 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\UI;

use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\Market\Shop;
use App\Models\Grouping\Organisation;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class OMSDashboard
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("oms.view");
    }


    public function asController()
    {
        return app('currentTenant');
    }

    public function inShop(Shop $shop, ActionRequest $request): Shop
    {
        return $shop;
    }


    public function htmlResponse(Organisation|Shop $scope, ActionRequest $request): Response
    {

        $container = null;
        if (class_basename($scope) == 'Shop') {
            $container = [
                'icon'    => ['fal', 'fa-store-alt'],
                'tooltip' => __('Shop'),
                'label'   => Str::possessive($scope->name)
            ];
        }

        return Inertia::render(
            'OMS/OMSDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => 'OMS',
                'pageHead'    => [
                    'title'     => __('Order management system'),
                    'container' => $container

                ],


            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {

        // dd($routeParameters);
        return match ($routeName) {
            'grp.oms.shops.show.dashboard' =>
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.oms.shops.show.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('OMS').' ('.$routeParameters['shop']->code.')',
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
                                'name' => 'grp.oms.dashboard'
                            ],
                            'label' => __('OMS').' ('.__('all shops').')',
                        ]
                    ]
                ]
            )
        };
    }

}
