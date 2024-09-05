<?php
/*
 * Author: Vika Aqordi <aqordivika@yahoo.co.id>
 * Created on: 15-08-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

namespace App\Actions\UI\Pupil\Dashboard;

use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDashboard
{
    use AsAction;


    public function asController(ActionRequest $request): Response
    {
        $shopifyUser = $request->user('pupil');
        // dd(session('_token'));
        return Inertia::render('Dashboard/PupilDashboard', [
            // 'shop'                  => $shopifyUser,
            // 'token'                 => session()->all(),
            'token_request'         => $request->get('token'),
            'user'                  => $shopifyUser,
            'showIntro'             => !Arr::get($shopifyUser->settings, 'webhooks'),
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
