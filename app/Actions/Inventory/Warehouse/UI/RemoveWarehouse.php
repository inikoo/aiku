<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Warehouse\UI;

use App\Actions\HumanResources\Employee\UI\ShowEmployee;
use App\Actions\InertiaAction;
use App\Models\HumanResources\Employee;
use App\Models\Inventory\Warehouse;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveWarehouse extends InertiaAction
{
    public function handle(Warehouse $warehouse): Warehouse
    {
        return $warehouse;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.edit");
    }

    public function asController(Warehouse $warehouse, ActionRequest $request): Warehouse
    {
        $this->initialisation($request);

        return $this->handle($warehouse);
    }


    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete Employee'),
            'text'        => __("This action will delete this Employee"),
            'route'       => $route
        ];
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function htmlResponse(Warehouse $warehouse, ActionRequest $request): Response
    {
        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete employee'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $warehouse
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-inventory'],
                            'title' => __('employee')
                        ],
                    'title' => $warehouse->slug,
                    'actions'=>[
                        [
                            'type'=>'button',
                            'style'=>'cancel',
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $this->routeName),
                                'parameters' => $warehouse->slug
                            ]
                        ]
                    ]
                ],
                'data'      => $this->getAction(
                    route:[
                        'name' => 'models.warehouse.delete',
                        'parameters' => array_values($this->originalParameters)
                    ]
                )
            ]
        );
    }


    public function getBreadcrumbs(Warehouse $warehouse): array
    {
        return ShowWarehouse::make()->getBreadcrumbs($warehouse, suffix: '('.__('deleting').')');
    }
}
