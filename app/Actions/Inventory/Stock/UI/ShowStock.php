<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 15:27:27 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Stock\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Inventory\Stock;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowStock extends InertiaAction
{
    use HasUIStock;

    private Stock $stock;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.stocks.edit');

        return $request->user()->hasPermissionTo("inventory.stocks.view");
    }

    public function asController(Stock $stock, ActionRequest $request): void
    {
        $stock->load('locations');
        $this->initialisation($request);
        $this->stock    = $stock;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();



        return Inertia::render(
            'Inventory/Stock',
            [
                'title'       => __('stock'),
                'breadcrumbs' => $this->getBreadcrumbs($this->stock),
                'pageHead'    => [
                    'icon'  => 'fal fa-box',
                    'title' => $this->stock->code,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                ],
                'stock'   => new StockResource($this->stock),
            ]
        );
    }


    public function jsonResponse(): StockResource
    {
        return new StockResource($this->stock);
    }
}
