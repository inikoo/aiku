<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 13:06:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\StockFamily\UI;

use App\Actions\InertiaAction;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateStockFamily extends InertiaAction
{
    use HasUIStockFamilies;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('inventory.stocks.edit');
    }

    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }

    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new SKU family'),
                'pageHead'    => [
                    'title'        => __('new SKU family'),
                    'icon'         => [
                        'title' => __("stock's families"),
                        'icon'  => 'fal fa-boxes-alt'
                    ],
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'grp.oms.stock-families.index',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('new family'),
                            'fields' => [
                                'code' => [
                                    'type'      => 'input',
                                    'label'     => __('code'),
                                    'required'  => true
                                ],
                                'name' => [
                                    'type'      => 'input',
                                    'label'     => __('name'),
                                    'required'  => true
                                ],
                            ]
                        ]
                    ],
                    'route' => [
                        'name' => 'grp.models.stock-family.store',
                    ]
                ],

            ]
        );
    }


    public function getBreadcrumbs(): array
    {
        return array_merge(
            IndexStockFamilies::make()->getBreadcrumbs(),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __("creating stock family"),
                    ]
                ]
            ]
        );
    }
}
