<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 25 Apr 2023 10:11:53 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\UI\Accounting;

use App\Actions\InertiaAction;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Enums\UI\CatalogueTabsEnum;
use App\Models\Market\Shop;
use Lorisleiva\Actions\ActionRequest;

class AccountingHub extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("accounting.view");
    }


    public function inTenant(ActionRequest $request): ActionRequest
    {
        $this->initialisation($request)->withTab(CatalogueTabsEnum::values());
        return $request;
    }

    public function inShop(Shop $shop, ActionRequest $request): ActionRequest
    {
        $this->initialisation($request)->withTab(CatalogueTabsEnum::values());
        return $request;
    }




    public function getBreadcrumbs($routeName, $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => $routeName,
                            'parameters' => $routeParameters
                        ],
                        'label' => __('catalogue'),
                        'icon'  => 'fal fa-folder-tree'
                    ],
                ],
            ];
        };


        return match ($routeName) {
            'shops' => array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                $headCrumb()
            ),
            'shops.show.hub' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($routeParameters['shop']),
                $headCrumb([$routeParameters['shop']->slug])
            ),
            default => []
        };
    }
}
