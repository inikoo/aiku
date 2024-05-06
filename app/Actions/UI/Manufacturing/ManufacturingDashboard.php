<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Manufacturing;

use App\Actions\OrgAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ManufacturingDashboard extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("productions.{$this->organisation->id}.view");
    }


    public function asController(Organisation $organisation, ActionRequest $request): Organisation
    {
        $this->initialisation($organisation, $request);

        return $organisation;
    }


    public function htmlResponse(): Response
    {
        return Inertia::render(
            'Manufacturing/ManufacturingDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs(),
                'title'        => __('manufacturing'),
                'pageHead'     => [
                    'title' => __('manufacturing'),
                ],
                //todo
                /*
                'flatTreeMaps' => [

                    [
                        [
                            'name'  => __('Stocks'),
                            'icon'  => ['fal', 'fa-flask'],
                            'href'  => ['grp.production.products.index'],
                            'index' => [
                                'number' => $this->organisation->manufactureStats->number_manufactured_stocks
                            ]
                        ]
                    ]
                ]
                */

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
                                'name' => 'grp.org.manufacturing.dashboard'
                            ],
                            'label' => __('manufacturing'),
                        ]
                    ]
                ]
            );
    }


}
