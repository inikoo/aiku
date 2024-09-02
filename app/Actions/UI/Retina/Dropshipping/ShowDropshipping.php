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
use App\Actions\UI\Retina\Dashboard\ShowDashboard;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowDropshipping extends RetinaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->is_root;
    }

    public function asController(ActionRequest $request)
    {
        $this->initialisation($request);

        return $request;
    }

    public function htmlResponse(ActionRequest $request): Response
    {
        /** @var \App\Models\CRM\Customer $customer */
        $customer = $request->user()->customer;

        return Inertia::render(
            'Dropshipping/DropshippingDashboard',
            [
                // 'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('Dropshipping'),
                'pageHead'    => [
                    'title' => __('Dropshipping'),
                    'icon'  => 'fal fa-parachute-box'
                ],
                'shopify_url' => '.' . config('shopify-app.myshopify_domain'),
                'createRoute' => [
                    'name'       => 'retina.dropshipping.shopify_user.store',
                    'parameters' => [],
                    'method'     => 'post'
                ],
                'unlinkRoute' => [
                    'name'       => 'retina.dropshipping.shopify_user.delete',
                    'parameters' => [],
                    'method'     => 'delete'
                ],
                'connectRoute' => $customer->shopifyUser ? [
                    'url'       => route('pupil.authenticate', [
                        'shop' => $customer->shopifyUser?->name
                    ])
                ] : null
            ]
        );
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
