<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 02 Jun 2023 14:31:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dropshipping;

use App\Actions\OrgAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DropshippingDashboard extends OrgAction
{
    use AsAction;
    use WithInertia;



    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.view");
    }


    public function asController(Organisation $organisation, ActionRequest $request): ActionRequest
    {
        $this->initialisation($organisation, $request);
        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {


        return Inertia::render(
            'Dropshipping/DropshippingDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('dropshipping'),
                'pageHead'    => [
                    'title' => __('dropshipping'),
                ],


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
                                'name' => 'dropshipping.dashboard'
                            ],
                            'label' => __('dropshipping'),
                        ]
                    ]
                ]
            );
    }



}
