<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dropshipping\Marketing;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Enums\UI\Dropshipping\MarketingTabsEnum;
use App\Models\Catalogue\Shop;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowMarketingDashboard extends OrgAction
{
    // public function authorize(ActionRequest $request): bool
    // {
    //     return $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.view");
    // }


    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): ActionRequest
    {
        $this->initialisationFromShop($shop, $request)->withTab(MarketingTabsEnum::values());

        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Shop/Dropshipping/MarketingDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'        => __('marketing'),
                'pageHead'     => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-bullhorn'],
                        'title' => __('marketing')
                    ],
                    'model'     => __('marketing'),
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-chart-network'],
                        'title' => __('marketing')
                    ],
                    'title' => __('dashboard'),
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => MarketingTabsEnum::navigation()
                ],
                'dashboard_stats'   => [
                    [
                        'name' => __('Newsletters'),
                        'value' => 0,  // TODO: Add the actual value
                        'icon'  => ['fal', 'fa-newspaper'],
                        'route' => [
                            'name'       => 'grp.org.shops.show.marketing.newsletters.index',
                            'parameters' => $request->route()->originalParameters()
                        ]
                    ],
                    [
                        'name' => __('Mailshots'),
                        'value' => 0,  // TODO: Add the actual value
                        'icon'  => ['fal', 'fa-mail-bulk'],
                        'route' => [
                            'name'       => 'grp.org.shops.show.marketing.mailshots.index',
                            'parameters' => $request->route()->originalParameters()
                        ]
                    ],
                ]


            ]
        );
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return
           array_merge(
               ShowShop::make()->getBreadcrumbs($routeParameters),
               [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.shops.show.marketing.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Offers')
                        ]
                    ]
                ]
           );
    }


}
