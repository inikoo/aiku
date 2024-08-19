<?php
/*
 * Author: Vika Aqordi <aqordivika@yahoo.co.id>
 * Created on: 19-08-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

namespace App\Actions\UI\Retina\Dropshipping;

use App\Actions\RetinaAction;
use App\Actions\Catalogue\Product\UI\IndexProducts as IndexUIProducts;
use App\Enums\UI\Catalogue\ProductTabsEnum;
use App\Http\Resources\Catalogue\ProductsResource;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class IndexProducts extends RetinaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->is_root;
    }

    public function asController(ActionRequest $request): ActionRequest
    {
        $this->initialisation($request);

        return $request;
    }

    public function htmlResponse(ActionRequest $request): Response
    {
        $shop = $request->get('website')->shop;

        return Inertia::render(
            'Dropshipping/Products',
            [
                // 'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('Products'),
                'pageHead'    => [
                    'title' => __('Products'),
                    'icon'  => 'fal fa-cube'
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => ProductTabsEnum::navigation()
                ],

                'products' => ProductsResource::collection(IndexUIProducts::run($shop, 'products'))
            ]
        )->table(IndexUIProducts::make()->tableStructure($shop, prefix: 'products'));
    }

    // public function getBreadcrumbs(): array
    // {
    //     return
    //         array_merge(
    //             ShowDashboard::make()->getBreadcrumbs(),
    //             [
    //                 [
    //                     'type'   => 'simple',
    //                     'simple' => [
    //                         'route' => [
    //                             'name' => 'retina.sysadmin.dashboard'
    //                         ],
    //                         'label'  => __('system administration'),
    //                     ]
    //                 ]
    //             ]
    //         );
    // }
}
