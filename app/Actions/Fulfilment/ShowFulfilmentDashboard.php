<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 08:29:42 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment;

use App\Actions\UI\WithInertia;
use App\Models\Central\Tenant;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;


class ShowFulfilmentDashboard
{
    use AsAction;
    use WithInertia;


    private ?Tenant $tenant;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.view");
    }


    public function asController(): void
    {
        $this->tenant = tenant();
    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Fulfilment/FulfilmentDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('fulfilment'),
                'pageHead'    => [
                    'title' => __('fulfilment'),
                ],
                'treeMaps'    => [

                    [
                        [
                            'name' => __('Products'),
                            'icon' => ['fal', 'fa-flask'],
                            'href' => ['fulfilment.products.index'],
                            'index' => [
                                'number' => $this->tenant->FulfilmentStats->number_products
                            ]
                        ]
                    ]
                ]

            ]

        );
    }


    public function getBreadcrumbs(): array
    {
        return [
            'fulfilment.dashboard' => [
                'route' => 'fulfilment.dashboard',
                'name'  => __('fulfilment'),
            ]
        ];
    }


}
