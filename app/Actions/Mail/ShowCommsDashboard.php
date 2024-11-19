<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:06:20 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Mail;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\OrgAction;
use App\Enums\UI\Mail\CommsDashboardTabsEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowCommsDashboard extends OrgAction
{
    use WithCommsSubNavigation;


    public function handle(ActionRequest $request): Shop
    {
        return $this->shop;
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request)
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($request);
    }

    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request)
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($request);
    }

    public function htmlResponse(Shop|Fulfilment $parent, ActionRequest $request): Response
    {
        return Inertia::render(
            'Mail/CommsDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()),
                'title'       => __('mail'),
                'pageHead'    => [
                    'icon'          => [
                        'icon'  => ['fal', 'fa-satellite-dish'],
                        'title' => __('comms')
                    ],
                    'iconRight'     => [
                        'icon'  => ['fal', 'fa-chart-network'],
                        'title' => __('dashboard')
                    ],
                    'title'         => __('Comms dashboard'),
                    'subNavigation' => $this->getCommsNavigation($parent),
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => CommsDashboardTabsEnum::navigation()
                ],


            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return match ($routeName) {
            'grp.org.shops.show.comms.dashboard' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.shops.show.comms.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Comms')
                        ]
                    ]
                ]
            ),
            'grp.org.fulfilments.show.comms.dashboard' =>
            array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.comms.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Comms')
                        ]
                    ]
                ]
            ),
            default => []
        };
    }


}
