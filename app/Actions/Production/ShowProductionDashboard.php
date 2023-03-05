<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 08:29:42 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Production;

use App\Actions\UI\WithInertia;
use App\Models\Central\Tenant;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowProductionDashboard
{
    use AsAction;
    use WithInertia;


    private ?Tenant $tenant;

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
                'treeMaps'    => [

                    [
                        [
                            'name'  => __('Products'),
                            'icon'  => ['fal', 'fa-flask'],
                            'href'  => ['production.products.index'],
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
        return [
            'production.dashboard' => [
                'route' => 'production.dashboard',
                'name'  => __('production'),
            ]
        ];
    }
}
