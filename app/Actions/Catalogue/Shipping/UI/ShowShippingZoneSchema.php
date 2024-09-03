<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:56:01 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shipping\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\Goods\HasGoodsAuthorisation;
use App\Actions\GrpAction;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Actions\UI\Goods\ShowGoodsDashboard;
use App\Enums\UI\Catalogue\ShippingZoneSchemaTabsEnum;
use App\Enums\UI\SupplyChain\TradeUnitTabsEnum;
use App\Http\Resources\Catalogue\ShippingZoneSchemaResource;
use App\Http\Resources\Goods\TradeUnitResource;
use App\Models\Catalogue\Shop;
use App\Models\Goods\TradeUnit;
use App\Models\Ordering\ShippingZoneSchema;
use App\Models\SupplyChain\Stock;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowShippingZoneSchema extends OrgAction
{
    use HasCatalogueAuthorisation;

    public function handle(ShippingZoneSchema $shippingZoneSchema): ShippingZoneSchema
    {
        return $shippingZoneSchema;
    }


    public function asController(Organisation $organisation, Shop $shop, ShippingZoneSchema $shippingZoneSchema, ActionRequest $request): ShippingZoneSchema
    {
        $this->initialisationFromShop($shop, $request)->withTab(TradeUnitTabsEnum::values());
        return $this->handle($shippingZoneSchema);
    }

    public function htmlResponse(ShippingZoneSchema $shippingZoneSchema, ActionRequest $request): Response
    {

        return Inertia::render(
            'Org/Catalogue/ShippingZoneSchema',
            [
                    'title'       => __('Shipping Zone Schema'),
                    'breadcrumbs' => $this->getBreadcrumbs(
                        $shippingZoneSchema,
                        $request->route()->getName(),
                        $request->route()->originalParameters()
                    ),
                    'navigation'  => [
                        'previous' => $this->getPrevious($shippingZoneSchema, $request),
                        'next'     => $this->getNext($shippingZoneSchema, $request),
                    ],
                    'pageHead'    => [
                        'icon'    => [
                            'title' => __('trade unit'),
                            'icon'  => 'fal fa-atom'
                        ],
                        'title'   => $shippingZoneSchema->name,
                        'actions' => [
                            $this->canEdit ? [
                                'type'  => 'button',
                                'style' => 'edit',
                                'route' => [
                                    'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                    'parameters' => array_values($request->route()->originalParameters())
                                ]
                            ] : false,
                            // $this->canDelete ? [
                            //     'type'  => 'button',
                            //     'style' => 'delete',
                            //     'route' => [
                            //         'name'       => 'grp.org.warehouses.show.inventory.org_stock_families.show.stocks.remove',
                            //         'parameters' => array_values($request->route()->originalParameters())
                            //     ]

                            // ] : false
                        ]
                    ],
                    'tabs'=> [
                        'current'    => $this->tab,
                        'navigation' => ShippingZoneSchemaTabsEnum::navigation()

                    ],
            ]
        );
    }


    public function jsonResponse(ShippingZoneSchema $shippingZoneSchema): ShippingZoneSchemaResource
    {
        return new ShippingZoneSchemaResource($shippingZoneSchema);
    }

    public function getBreadcrumbs(ShippingZoneSchema $shippingZoneSchema, string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (ShippingZoneSchema $shippingZoneSchema, array $routeParameters, $suffix) {
            return [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Shippings')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $shippingZoneSchema->slug,
                        ],
                    ],
                    'suffix' => $suffix,

                ],
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.assets.shipping.show' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs( $routeParameters),
                $headCrumb(
                    $shippingZoneSchema,
                    [
                        'index' => [
                            'name'       => preg_replace('/show$/', 'index', $routeName),
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => $routeName,
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(ShippingZoneSchema $shippingZoneSchema, ActionRequest $request): ?array
    {
        $previous = ShippingZoneSchema::where('slug', '<', $shippingZoneSchema->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(ShippingZoneSchema $shippingZoneSchema, ActionRequest $request): ?array
    {
        $next = ShippingZoneSchema::where('slug', '>', $shippingZoneSchema->slug)->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?ShippingZoneSchema $shippingZoneSchema, string $routeName): ?array
    {
        if (!$shippingZoneSchema) {
            return null;
        }


        return match ($routeName) {
            'grp.org.shops.show.assets.shipping.show' => [
                'label' => $shippingZoneSchema->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $shippingZoneSchema->organisation->slug,
                        'shop'         => $shippingZoneSchema->shop->slug,
                        'shippingZoneSchema' => $shippingZoneSchema->slug
                    ]
                ]
            ],
        };
    }
}
