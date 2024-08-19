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

class ShowProducts extends RetinaAction
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
            'Dropshipping/Products',
            [
                // 'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('Products'),
                'pageHead'    => [
                    'title' => __('Products'),
                    'icon'  => 'fal fa-cube'
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
