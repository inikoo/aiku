<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 06 Aug 2024 16:07:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\UI;

use App\Actions\Goods\StockFamily\UI\ShowStockFamily;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\Inventory\HasInventoryAuthorisation;
use App\Actions\Inventory\UI\ShowInventoryDashboard;
use App\Actions\OrgAction;
use App\Enums\UI\Procurement\OrgStockTabsEnum;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Inventory\OrgStockResource;
use App\Models\Inventory\OrgStock;
use App\Models\SupplyChain\StockFamily;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowOrgStock extends OrgAction
{
    use HasInventoryAuthorisation;


    private Organisation|StockFamily $parent;

    public function handle(OrgStock $orgStock): OrgStock
    {
        return $orgStock;
    }


    public function asController(Organisation $organisation, OrgStock $orgStock, ActionRequest $request): OrgStock
    {
        $this->parent = $organisation;
        $this->initialisation($this->parent, $request)->withTab(OrgStockTabsEnum::values());

        return $this->handle($orgStock);
    }

    public function current(Organisation $organisation, OrgStock $orgStock, ActionRequest $request): OrgStock
    {
        $this->parent = $organisation;
        $this->initialisation($this->parent, $request)->withTab(OrgStockTabsEnum::values());

        return $this->handle($orgStock);
    }

    public function inStockFamily(Organisation $organisation, StockFamily $orgStockFamily, OrgStock $orgStock, ActionRequest $request): OrgStock
    {
        $this->parent = $orgStockFamily;
        $this->initialisation($organisation, $request);

        return $this->handle($orgStock);
    }

    public function htmlResponse(OrgStock $orgStock, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Inventory/OrgStock',
            [
                'title'                         => __('stock'),
                'breadcrumbsx'                  => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigationx'                   => [
                    'previous' => $this->getPrevious($orgStock, $request),
                    'next'     => $this->getNext($orgStock, $request),
                ],
                'pageHead'                     => [
                    'icon'    => [
                        'title' => __('sku'),
                        'icon'  => 'fal fa-box'
                    ],
                    'title'   => $orgStock->slug,

                ],
                'tabs'                         => [
                    'current'    => $this->tab,
                    'navigation' => OrgStockTabsEnum::navigation()

                ],
                OrgStockTabsEnum::SHOWCASE->value => $this->tab == OrgStockTabsEnum::SHOWCASE->value ?
                    fn () => GetOrgStockShowcase::run($orgStock)
                    : Inertia::lazy(fn () => GetOrgStockShowcase::run($orgStock)),

                OrgStockTabsEnum::HISTORY->value => $this->tab == OrgStockTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($orgStock))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($orgStock)))


            ]
        )->table();
    }


    public function jsonResponse(OrgStock $orgStock): OrgStockResource
    {
        return new OrgStockResource($orgStock);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (OrgStock $orgStock, array $routeParameters, $suffix) {
            return [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('SKUs')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $orgStock->slug,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ];
        };


        return match ($routeName) {
            'grp.goods.stocks.show' =>
            array_merge(
                (new ShowInventoryDashboard())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $routeParameters['orgStock'],
                    [
                        'index' => [
                            'name'       => 'grp.org.inventory.org_stocks.all_org_stocks.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'grp.org.inventory.org-stocks.show',
                            'parameters' => [
                                $routeParameters['orgStock']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.inventory.org_stock_families.show.stocks.show' =>
            array_merge(
                (new ShowStockFamily())->getBreadcrumbs($routeParameters['stockFamily']),
                $headCrumb(
                    $routeParameters['stock'],
                    [
                        'index' => [
                            'name'       => 'grp.org.inventory.org_stock_families.show.org_stocks.index',
                            'parameters' => [
                                $routeParameters['orgStockFamily']->slug
                            ]
                        ],
                        'model' => [
                            'name'       => 'grp.org.inventory.org_stock_families.show.stocks.show',
                            'parameters' => [
                                $routeParameters['orgStockFamily']->slug,
                                $routeParameters['orgStock']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(OrgStock $orgStock, ActionRequest $request): ?array
    {
        $previous = OrgStock::where('code', '<', $orgStock->code)->when(true, function ($query) use ($orgStock, $request) {
            if ($request->route()->getName() == 'grp.org.inventory.org_stock_families.show.stocks.show') {
                $query->where('org_stock_family_id', $orgStock->orgStockFamily->id);
            }
        })->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(OrgStock $orgStock, ActionRequest $request): ?array
    {
        $next = OrgStock::where('code', '>', $orgStock->code)->when(true, function ($query) use ($orgStock, $request) {
            if ($request->route()->getName() == 'grp.org.inventory.org_stock_families.show.stocks.show') {
                $query->where('org_stock_family_id', $orgStock->orgStockFamily->id);
            }
        })->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?OrgStock $orgStock, string $routeName): ?array
    {
        if (!$orgStock) {
            return null;
        }

        return match ($routeName) {
            'grp.org.inventory.org_stocks.current_org_stocks.show',
            'grp.org.inventory.org-stocks.show' => [
                'label' => $orgStock->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'stock' => $orgStock->slug
                    ]
                ]
            ],
            'grp.org.inventory.org_stock_families.show.stocks.show' => [
                'label' => $orgStock->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'stockFamily' => $orgStock->orgStockFamily->slug,
                        'stock'       => $orgStock->slug
                    ]

                ]
            ]
        };
    }
}
