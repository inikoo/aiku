<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:40:27 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\UI;

use App\Actions\UI\WithInertia;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class OMSDashboard
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("oms.view");
    }


    public function asController(ActionRequest $request): void
    {

    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();



        return Inertia::render(
            'OMS/OMSDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => 'OMS',
                'pageHead'    => [
                    'title' => __('Order management system'),
                ],


            ]
        );
    }


    public function getBreadcrumbs(): array
    {
        return [
            'oms.hub' => [
                'route' => 'oms.dashboard',
                'name'  => 'OMS',
            ]
        ];
    }
}
