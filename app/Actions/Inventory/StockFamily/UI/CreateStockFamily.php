<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Actions\InertiaAction;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateStockFamily extends InertiaAction
{
    use HasUIStockFamilies;


    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new stock family'),
                'pageHead'    => [
                    'title'        => __('new stock family'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'inventory.stock-families.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],


            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('inventory.stocks.edit');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }
}
