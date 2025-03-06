<?php

/*
 * Author: Vika Aqordi <aqordivika@yahoo.co.id>
 * Created on: 15-08-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

namespace App\Actions\Pupil\Dashboard;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowPupilDashboard
{
    use AsAction;


    public function asController(ActionRequest $request): Response
    {
        /** @var \App\Models\Dropshipping\ShopifyUser $shopifyUser */
        $shopifyUser = $request->user('pupil');

        return Inertia::render('Dashboard/PupilWelcome', [
             'shop'                  => $shopifyUser->customer->shop->name,
             'shopUrl'                  => 'https://'.$shopifyUser->customer->shop->website->domain . '/app',
            'user'                  => $shopifyUser,
            'showIntro'             => !Arr::get($shopifyUser->settings, 'webhooks'),
            'shops' => Shop::where('type', ShopTypeEnum::FULFILMENT->value)->get(),
            'routes'                => [
                'products' => [
                    'name'       => 'pupil.products',
                    'parameters' => [
                        'shopifyUser' => $shopifyUser->id
                    ]
                ],
                'store_product' => [
                    'name'       => 'pupil.shopify_user.product.store',
                    'parameters' => [
                        'shopifyUser' => $shopifyUser->id
                    ]
                ],
                'get_started' => [
                    'name'       => 'pupil.shopify_user.get_started.store',
                    'parameters' => [
                        'shopifyUser' => $shopifyUser->id
                    ],
                    'method'    => 'post'
                ]
            ]
        ]);
    }

    // public function getBreadcrumbs($label = null): array
    // {
    //     return [
    //         [

    //             'type'   => 'simple',
    //             'simple' => [
    //                 'icon'  => 'fal fa-home',
    //                 'label' => $label,
    //                 'route' => [
    //                     'name' => 'retina.dashboard.show'
    //                 ]
    //             ]

    //         ],

    //     ];
    // }
}
