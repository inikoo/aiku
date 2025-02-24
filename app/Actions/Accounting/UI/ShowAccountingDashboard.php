<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Accounting\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\Comms\Traits\WithAccountingSubNavigation;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\OrgAction;
use App\Enums\UI\Mail\AccoutingDashboardTabsEnum;
use App\Enums\UI\Mail\CommsDashboardTabsEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowAccountingDashboard extends OrgAction
{
    use WithAccountingSubNavigation;


    public function handle(Shop|Fulfilment $parent): Shop|Fulfilment
    {
        return $parent;
    }


    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Shop
    {
        $this->initialisationFromShop($shop, $request)->withTab(CommsDashboardTabsEnum::values());

        return $this->handle($shop);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): Fulfilment
    {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(CommsDashboardTabsEnum::values());

        return $this->handle($fulfilment);
    }

    public function inShop(Organisation $organisation, Shop $shop, ActionRequest $request): Shop
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop);
    }

    public function htmlResponse(Shop|Fulfilment $parent, ActionRequest $request): Response
    {

        $subNavigation = [];
        if ($parent instanceof Shop) {
            $subNavigation = $this->getSubNavigationShop($parent);
        } elseif ($parent instanceof Fulfilment) {
            $subNavigation = $this->getSubNavigation($parent);
        }
        return Inertia::render(
            'Comms/AccountingDashboard',
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
                    'title'         => __('Accounting dashboard'),
                    'subNavigation' => $subNavigation,
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => AccoutingDashboardTabsEnum::navigation()
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
            'grp.org.fulfilments.show.comms.dashboard', 'grp.org.fulfilments.show.operations.comms.dashboard' =>
            array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.operations.comms.dashboard',
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
