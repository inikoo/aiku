<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dropshipping\Assets;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Enums\UI\Dropshipping\AssetsTabsEnum;
use App\Models\Catalogue\Shop;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowAssetDashboard extends OrgAction
{
    // public function authorize(ActionRequest $request): bool
    // {
    //     return $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.view");
    // }


    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): ActionRequest
    {
        $this->initialisationFromShop($shop, $request);

        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Shop/Dropshipping/AssetsDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'        => __('assets'),
                'pageHead'     => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-ballot'],
                        'title' => __('asset')
                    ],
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-chart-network'],
                        'title' => __('asset')
                    ],
                    'title' => __('assets dashboard'),
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => AssetsTabsEnum::navigation()
                ],


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
                                'name'       => 'grp.org.shops.show.assets.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Assets')
                        ]
                    ]
                ]
           );
    }


}
