<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Stock\UI;

use App\Actions\InertiaAction;
use App\Models\Inventory\Stock;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditStock extends InertiaAction
{
    use HasUIStock;
    public function handle(Stock $stock): Stock
    {
        return $stock;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.stocks.edit');
        return $request->user()->hasPermissionTo("inventory.stocks.view");
    }

    public function asController(Stock $stock, ActionRequest $request): Stock
    {
        $this->initialisation($request);

        return $this->handle($stock);
    }



    public function htmlResponse(Stock $stock): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('stock'),
                'breadcrumbs' => $this->getBreadcrumbs($stock),
                'pageHead'    => [
                    'title'     => $stock->code,
                    'exitEdit'  => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ],


                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [
                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $stock->code
                                ],
                                'quantity' => [
                                    'type'  => 'input',
                                    'label' => __('location'),
                                    'value' => $stock->locations
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'models.stock.update',
                            'parameters'=> $stock->slug

                        ],
                    ]
                ]
            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowStock::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('editing').')'
        );
    }
}
