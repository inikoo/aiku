<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Sep 2023 14:12:54 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\UI\Dashboard;

use App\Actions\Fulfilment\FulfilmentCustomer\UI\GetFulfilmentCustomerShowcase;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowRetinaDashboard
{
    use AsAction;


    public function asController(ActionRequest $request): Response
    {
        $fulfilmentCustomer = auth()->user()->customer->fulfilmentCustomer;

        return Inertia::render(
            'Dashboard/RetinaDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    __('Home')
                ),
                'data'       => GetFulfilmentCustomerShowcase::run($fulfilmentCustomer, $request),
            ]
        );
    }

    public function getBreadcrumbs($label = null): array
    {
        return [
            [

                'type'   => 'simple',
                'simple' => [
                    'icon'  => 'fal fa-home',
                    'route' => [
                        'name' => 'retina.dashboard.show'
                    ]
                ]

            ],

        ];
    }
}
