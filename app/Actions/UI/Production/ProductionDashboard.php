<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Production;

use App\Actions\OrgAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ProductionDashboard extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("production.view");
    }


    public function asController(Organisation $organisation, ActionRequest $request): Organisation
    {
        $this->initialisation($organisation, $request);
        return $organisation;
    }



    public function htmlResponse(): Response
    {

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
                                'number' => $this->organisation->productionStats->number_products
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
