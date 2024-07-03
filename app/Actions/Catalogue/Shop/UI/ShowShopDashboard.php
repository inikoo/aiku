<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\UI;

use App\Actions\OrgAction;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowShopDashboard extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("products.{$this->shop->id}.view");
    }


    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): ActionRequest
    {
        $this->initialisationFromShop($shop, $request);

        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Catalogue/ShopDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()),
                'title'        => __('shop'),
                'pageHead'     => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-hand-holding-box'],
                        'title' => __('shop')
                    ],
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-chart-network'],
                        'title' => __('shop')
                    ],
                    'title' => __('shop central command'),
                ],


            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.shops.show.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Dashboard'),
                            'icon'  => 'fal fa-chart-network'
                        ]
                    ]
                ]
            );

    }


}
