<?php

/** @noinspection PhpUnusedParameterInspection */

/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 11 Apr 2023 08:24:46 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\OrgStock\UI;

use App\Actions\InertiaAction;
use App\Models\SupplyChain\StockFamily;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateStock extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('inventory.stocks.edit');
    }

    public function inStockFamily(StockFamily $stockFamily, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($request);
    }

    public function handle(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'    => __('new stock'),
                'icon'     =>
                    [
                        'icon'  => ['fal', 'fa-box'],
                        'title' => __('SKU')
                    ],
                'pageHead' => [
                    'title'        => __('new SKU'),
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'grp.org.inventory.org-stock-families.show.stocks.index',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('new SKU'),
                            'fields' => [
                                'code' => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'required' => true
                                ],
                                'name' => [
                                    'type'     => 'input',
                                    'label'    => __('name'),
                                    'required' => true
                                ],
                            ]
                        ]
                    ],
                    'route' => match ($request->route()->getName()) {
                        'grp.org.inventory.org-stock-families.show.stocks.create' => [
                            'name'      => 'grp.models.stock-family.stock.store',
                            'arguments' => [$request->route()->originalParameters()['stockFamily']->slug]
                        ],
                        default => [
                            'name'      => 'grp.models.stock.store',
                            'arguments' => []
                        ]
                    }
                ],

            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexOrgStocks::make()->getBreadcrumbs(
                routeName: preg_replace('/create$/', 'index', $routeName),
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating stock'),
                    ]
                ]
            ]
        );
    }
}
