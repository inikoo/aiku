<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Actions\InertiaAction;
use App\Models\Inventory\StockFamily;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveStockFamily extends InertiaAction
{
    public function handle(StockFamily $warehouse): StockFamily
    {
        return $warehouse;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.edit");
    }

    public function asController(StockFamily $stockFamily, ActionRequest $request): StockFamily
    {
        $this->initialisation($request);

        return $this->handle($stockFamily);
    }


    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete Stock Family'),
            'text'        => __("This action will delete this Stock Family and its Stocks"),
            'route'       => $route
        ];
    }

    public function htmlResponse(StockFamily $stockFamily, ActionRequest $request): Response
    {
        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete stock family'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $stockFamily
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-boxes-alt'],
                            'title' => __('stock family')
                        ],
                    'title'  => $stockFamily->name,
                    'actions'=> [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $request->route()->getName()),
                                'parameters' => $stockFamily->slug
                            ]
                        ]
                    ]
                ],
                'data'      => $this->getAction(
                    route:[
                        'name'       => 'grp.models.stock-family.delete',
                        'parameters' => $request->route()->originalParameters()
                    ]
                )
            ]
        );
    }

    public function getBreadcrumbs(StockFamily $stockFamily): array
    {
        return ShowStockFamily::make()->getBreadcrumbs($stockFamily, suffix: '('.__('deleting').')');
    }
}
