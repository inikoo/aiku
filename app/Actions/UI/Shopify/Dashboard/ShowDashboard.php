<?php
/*
 * Author: Vika Aqordi <aqordivika@yahoo.co.id>
 * Created on: 15-08-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

namespace App\Actions\UI\Shopify\Dashboard;

use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDashboard
{
    use AsAction;


    public function asController(ActionRequest $request): Response
    {
        $shopifyUser = $request->user('shopify');

        return Inertia::render('Dashboard/PupilDashboard', [
            'shop'          => $shopifyUser,
            'routes'        => [
                'products' => [
                    'name'       => 'shopify.products',
                    'parameters' => [
                        'shopifyUser' => $shopifyUser->id
                    ]
                ],
                'store_product' => [
                    'name'       => 'shopify.shopify_user.product.store',
                    'parameters' => [
                        'shopifyUser' => $shopifyUser->id
                    ]
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
