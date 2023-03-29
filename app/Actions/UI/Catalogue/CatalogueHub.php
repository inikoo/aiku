<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Catalogue;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CatalogueHub extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("showroom.view");
    }


    public function asController(ActionRequest $request): ActionRequest
    {
        $this->initialisation($request);
        return $request;
    }

    public function inShop(Shop $shop, ActionRequest $request): ActionRequest
    {
        $this->initialisation($request);
        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'CRM/CRMDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $request->route()->parameters()),
                'title'       => __('catalogue'),
                'pageHead'    => [
                    'title' => __('catalogue'),
                ],


            ]
        );
    }


    public function getBreadcrumbs($routeName, $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('catalogue')
                    ]
                ],
            ];
        };



        return match ($routeName) {
            'catalogue.hub'            => $headCrumb(),
            'shops.show.catalogue.hub' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($routeParameters['shop']),
                $headCrumb([$routeParameters['shop']->slug])
            ),
            default => []
        };
    }
}
