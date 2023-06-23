<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:40:27 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\UI;

use App\Actions\UI\Dashboard\Dashboard;
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


    public function asController(ActionRequest $request): ActionRequest
    {
        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'OMS/OMSDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => 'OMS',
                'pageHead'    => [
                    'title' => __('Order management system'),
                ],


            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return match ($routeName) {
            'oms.shops.show.dashboard' =>
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'oms.shops.show.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('OMS').' ('.$routeParameters['shop']->code.')',
                        ]
                    ]
                ]
            ),
            default =>
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'oms.dashboard'
                            ],
                            'label' => __('OMS').' ('.__('all shops').')',
                        ]
                    ]
                ]
            )
        };
    }

}
