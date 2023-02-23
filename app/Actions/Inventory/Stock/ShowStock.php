<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:46:59 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Stock;

use App\Actions\Inventory\ShowInventoryDashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Inventory\Stock;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;



class ShowStock
{
    use AsAction;
    use WithInertia;



    private Stock $stock;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.view");
    }

    public function asController(Stock $stock): void
    {
        $stock->load('locations');
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
                ],
                'stock'   => new StockResource($this->stock),
            ]
        );
    }


    #[Pure] public function jsonResponse(): StockResource
    {
        return new StockResource($this->stock);
    }


    public function getBreadcrumbs(Stock $stock): array
    {
        return array_merge(
            (new ShowInventoryDashboard())->getBreadcrumbs(),
            [
                'inventory.stocks.show' => [
                    'route'           => 'inventory.stocks.show',
                    'routeParameters' => $stock->id,
                    'name'            => $stock->code,
                    'index'           => [
                        'route'   => 'inventory.stocks.index',
                        'overlay' => __('stocks list')
                    ],
                    'modelLabel'      => [
                        'label' => __('stock')
                    ],
                ],
            ]
        );
    }

}
