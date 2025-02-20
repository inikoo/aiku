<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Feb 2025 18:21:56 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock\UI;

use App\Models\Goods\Stock;
use Lorisleiva\Actions\ActionRequest;

trait WithStockNavigation
{
    public function getPrevious(Stock $stock, ActionRequest $request): ?array
    {
        $previous = optional($stock->code, function ($code) use ($stock, $request) {
            return Stock::where('code', '<', $code)
                ->when(
                    $request->route()->getName() === 'grp.org.warehouses.show.inventory.org_stock_families.show.stocks.show',
                    fn ($query) => $query->where('stock_family_id', $stock->stockFamily->id)
                )
                ->orderBy('code', 'desc')
                ->first();
        });

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Stock $stock, ActionRequest $request): ?array
    {
        $next = optional($stock->code, function ($code) use ($stock, $request) {
            return Stock::where('code', '>', $code)
                ->when(
                    $request->route()->getName() === 'grp.org.warehouses.show.inventory.org_stock_families.show.stocks.show',
                    fn ($query) => $query->where('stock_family_id', $stock->stockFamily->id)
                )
                ->orderBy('code')
                ->first();
        });

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Stock $stock, string $routeName): ?array
    {
        if (!$stock) {
            return null;
        }


        return match ($routeName) {
            'grp.goods.stocks.show',
            'grp.goods.stocks.active_stocks.show',
            'grp.goods.stocks.in_process_stocks.show',
            'grp.goods.stocks.discontinuing_stocks.show',
            'grp.goods.stocks.discontinued_stocks.show',
            'grp.goods.stocks.edit',
            'grp.goods.stocks.active_stocks.edit',
            'grp.goods.stocks.in_process_stocks.edit',
            'grp.goods.stocks.discontinuing_stocks.edit',
            'grp.goods.stocks.discontinued_stocks.edit',
            => [
                'label' => $stock->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'stock' => $stock->slug
                    ]
                ]
            ],
            'grp.goods.org_stock_families.show.stocks.show' => [
                'label' => $stock->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'stockFamily'   => $stock->stockFamily->slug,
                        'stock'         => $stock->slug
                    ]

                ]
            ]
        };
    }

}
