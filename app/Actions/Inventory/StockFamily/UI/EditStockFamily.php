<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Actions\InertiaAction;

use App\Models\Inventory\StockFamily;
use Inertia\Inertia;
use Inertia\Response;

use Lorisleiva\Actions\ActionRequest;

class EditStockFamily extends InertiaAction
{
    public function handle(StockFamily $stockFamily): StockFamily
    {
        return $stockFamily;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.stock-families.edit");
    }

    public function asController(StockFamily $stockFamily, ActionRequest $request): StockFamily
    {
        $this->initialisation($request);

        return $this->handle($stockFamily);
    }



    public function htmlResponse(StockFamily $stockFamily, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'                            => __('stock family'),
                'breadcrumbs'                      => $this->getBreadcrumbs($stockFamily),
                'navigation'                       => [
                'previous' => $this->getPrevious($stockFamily, $request),
                'next'     => $this->getNext($stockFamily, $request),
                ],
                'pageHead'    => [
                    'title'     => $stockFamily->name,
                    'icon'      => [
                        'title' => __("stock's families"),
                        'icon'  => 'fal fa-boxes-alt'
                    ],
                    'actions'   => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ]
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('edit stock family'),
                            'fields' => [
                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $stockFamily->code
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('label'),
                                    'value' => $stockFamily->name
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'grp.models.stock-family.update',
                            'parameters'=> $stockFamily->slug

                        ],
                    ]
                ]
            ]
        );
    }

    public function getBreadcrumbs(StockFamily $stockFamily): array
    {
        return ShowStockFamily::make()->getBreadcrumbs(
            stockFamily: $stockFamily,
            suffix: '('.__('editing').')'
        );
    }

    public function getPrevious(StockFamily $stockFamily, ActionRequest $request): ?array
    {
        $previous = StockFamily::where('code', '<', $stockFamily->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(StockFamily $stockFamily, ActionRequest $request): ?array
    {
        $next = StockFamily::where('code', '>', $stockFamily->code)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?StockFamily $stockFamily, string $routeName): ?array
    {
        if (!$stockFamily) {
            return null;
        }

        return match ($routeName) {
            'grp.oms.stock-families.edit' => [
                'label' => $stockFamily->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'stockFamily' => $stockFamily->slug
                    ]

                ]
            ]
        };
    }
}
