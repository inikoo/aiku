<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Dispatch;

use App\Actions\UI\WithInertia;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDispatchHub
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("osm.view");
    }


    public function asController(ActionRequest $request): void
    {

    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();



        return Inertia::render(
            'Dispatch/DispatchHub',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => 'dispatch',
                'pageHead'    => [
                    'title' => __('Dispatch'),
                ],


            ]
        );
    }


    public function getBreadcrumbs(): array
    {
        return [
            'dispatch.hub' => [
                'route' => 'dispatch.hub',
                'name'  => __('dispatch'),
            ]
        ];
    }
}
