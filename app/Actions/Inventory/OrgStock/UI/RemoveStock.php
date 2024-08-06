<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\UI;

use App\Actions\Goods\Stock\UI\ShowStock;
use App\Actions\InertiaAction;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveStock extends InertiaAction
{
    public function handle(Stock $stock): Stock
    {
        return $stock;
    }

    public function authorize(ActionRequest $request): bool
    {

        return $request->user()->hasPermissionTo("inventory.stocks.edit");
    }

    public function asController(Stock $stock, ActionRequest $request): Stock
    {
        $this->initialisation($request);

        return $this->handle($stock);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inStockFamily(StockFamily $stockFamily, Stock $stock, ActionRequest $request): Stock
    {
        $this->initialisation($request);

        return $this->handle($stock);
    }

    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete SKU'),
            'text'        => __("This action will delete this SKU and its dependant"),
            'route'       => $route
        ];
    }

    public function htmlResponse(Stock $stock, ActionRequest $request): Response
    {

        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete sku'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-box'],
                            'title' => __('sku')
                        ],
                    'title'  => $stock->slug,
                    'actions'=> [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ]
                ],
                'data'     => $this->getAction(
                    route:
                    match ($request->route()->getName()) {
                        'grp.org.inventory.org-stocks.remove' => [
                            'name'       => 'grp.models.location.delete',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'grp.org.inventory.org_stock_families.show.stocks.remove' => [
                            'name'       => 'grp.models.stock-family.stock.delete',
                            'parameters' => $request->route()->originalParameters()
                        ]
                    }
                )
            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowStock::make()->getBreadcrumbs(
            routeName: preg_replace('/remove$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('deleting').')'
        );
    }
}
