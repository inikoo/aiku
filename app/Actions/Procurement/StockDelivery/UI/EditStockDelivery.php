<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\StockDelivery\UI;

use App\Actions\InertiaAction;
use App\Models\Procurement\StockDelivery;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditStockDelivery extends InertiaAction
{
    public function handle(StockDelivery $stockDelivery): StockDelivery
    {
        return $stockDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('procurement.edit');
        return $request->user()->hasPermissionTo("procurement.view");
    }

    public function asController(StockDelivery $stockDelivery, ActionRequest $request): StockDelivery
    {
        $this->initialisation($request);

        return $this->handle($stockDelivery);
    }

    public function htmlResponse(StockDelivery $stockDelivery, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'                                 => __('supplier delivery'),
                'navigation'                            => [
                    'previous' => $this->getPrevious($stockDelivery, $request),
                    'next'     => $this->getNext($stockDelivery, $request),
                ],
                'pageHead'    => [
                    'title'     => $stockDelivery->reference,
                    'actions'   => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ],


                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [
                                'number' => [
                                    'type'  => 'input',
                                    'label' => __('number'),
                                    'value' => $stockDelivery->number
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'grp.models.supplier-delivery.update',
                            'parameters'=> $stockDelivery->slug

                        ],
                    ]
                ]
            ]
        );
    }

    public function getPrevious(StockDelivery $stockDelivery, ActionRequest $request): ?array
    {
        $previous = StockDelivery::where('number', '<', $stockDelivery->number)->orderBy('number', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(StockDelivery $stockDelivery, ActionRequest $request): ?array
    {
        $next = StockDelivery::where('number', '>', $stockDelivery->number)->orderBy('number')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?StockDelivery $stockDelivery, string $routeName): ?array
    {
        if(!$stockDelivery) {
            return null;
        }
        return match ($routeName) {
            'grp.org.procurement.stock_deliveries.edit'=> [
                'label'=> $stockDelivery->reference,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'employee'=> $stockDelivery->number
                    ]

                ]
            ]
        };
    }
}
