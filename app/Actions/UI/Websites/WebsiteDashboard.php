<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 07 Jun 2023 02:41:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Websites;

use App\Actions\UI\Dashboard\Dashboard;
use App\Actions\UI\WithInertia;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class WebsiteDashboard
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("websites.view");
    }


    public function handle($scope)
    {
        return $scope;
    }

    public function asController(Website $website): Website
    {
        return $website;
    }


    public function htmlResponse(Website $website, ActionRequest $request): Response
    {
        return Inertia::render(
            'Web/WebsiteDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs(
                    $request->route()->parameters
                ),
                'title'        => __('websites dashboard'),
                'pageHead'     => [
                    'title' => __('website dashboard'),
                ],
                'flatTreeMaps' => [
                    [


                        [
                            'name'  => __('webpages'),
                            'icon'  => ['fal', 'fa-browser'],
                            'href'  => ['web.websites.show.webpages.index', $website->slug],
                            'index' => [
                                'number' => $website->stats->number_webpages
                            ]

                        ],


                    ]
                ]

            ]
        );
    }


    public function getBreadcrumbs(array $routeParameters): array
    {
        return
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'web.websites.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Website dashboard')
                        ]
                    ]
                ]
            );
    }

}
