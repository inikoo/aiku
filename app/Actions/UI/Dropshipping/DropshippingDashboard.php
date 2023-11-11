<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 02 Jun 2023 14:31:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dropshipping;

use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\Organisation\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DropshippingDashboard
{
    use AsAction;
    use WithInertia;


    private ?Organisation $organisation;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.view");
    }


    public function asController(): void
    {
        $this->tenant = app('currentTenant');
    }


    public function htmlResponse(): Response
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
