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

    public function asController(ActionRequest $request): void
    {
        $this->initialisation($request);
    }

    public function htmlResponse(): Response
    {
        return Inertia::render(
            'Dropshipping/DropshippingDashboard',
            [
                // 'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('system administration'),
                'pageHead'    => [
                    'title' => __('system administration'),
                ],

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
