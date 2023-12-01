<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Production;

use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\Organisation\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductionDashboard
{
    use AsAction;
    use WithInertia;


    private ?Organisation $organisation;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("production.view");
    }


    public function asController(): void
    {
        $this->tenant = app('currentTenant');
    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Production/ProductionDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('production'),
                'pageHead'    => [
                    'title' => __('production'),
                ],
                'flatTreeMaps'    => [

                    [
                        [
                            'name'  => __('Products'),
                            'icon'  => ['fal', 'fa-flask'],
                            'href'  => ['grp.production.products.index'],
                            'index' => [
                                'number' => $this->tenant->productionStats->number_products
                            ]
                        ]
                    ]
                ]

            ]
        );
    }

    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'grp.production.dashboard'
                            ],
                            'label' => __('production'),
                        ]
                    ]
                ]
            );
    }


}
