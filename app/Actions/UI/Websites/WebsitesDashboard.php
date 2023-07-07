<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 06 Jun 2023 23:52:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Websites;

use App\Actions\UI\Dashboard\Dashboard;
use App\Actions\UI\WithInertia;
use App\Models\Tenancy\Tenant;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class WebsitesDashboard
{
    use AsAction;
    use WithInertia;

    public function handle($scope)
    {
        return $scope;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("websites.view");
    }


    public function asController(): Tenant
    {
        return app('currentTenant');
    }



    public function htmlResponse(Tenant $tenant, ActionRequest $request): Response
    {

        return Inertia::render(
            'Web/WebsitesDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('websites dashboard'),
                'pageHead'    => [
                    'title'     => __('websites dashboard'),
                ],
                'flatTreeMaps' => [
                    [

                        [
                            'name'  => __('websites'),
                            'icon'  => ['fal', 'fa-globe'],
                            'href'  => ['web.websites.index'],
                            'index' => [
                                'number' => $tenant->webStats->number_websites
                            ]

                        ],



                    ]
]

            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {


        return match ($routeName) {
            'websites.show.dashboard' =>
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'websites.show.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Website dashboard')
                        ]
                    ]
                ]
            ),
            default =>
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'web.dashboard'
                            ],
                            'label' => __('websites dashboard'),
                        ]
                    ]
                ]
            )
        };
    }

}
