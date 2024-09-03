<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:26:52 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Charge\UI;

use App\Actions\Catalogue\Charge\UI\ShowCharge;
use App\Actions\Catalogue\Shipping\UI\ShowShippingZoneSchema;
use App\Actions\Goods\HasGoodsAuthorisation;
use App\Actions\GrpAction;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Actions\UI\Iris\ShowShipping;
use App\Enums\Ordering\ShippingZoneSchema\ShippingZoneSchemaTypeEnum;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Shop;
use App\Models\Goods\TradeUnit;
use App\Models\Ordering\ShippingZoneSchema;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditCharge extends OrgAction
{
    use HasCatalogueAuthorisation;

    public function handle(Charge $charge): Charge
    {
        return $charge;
    }

    public function asController(Organisation $organisation, Shop $shop, Charge $charge, ActionRequest $request): Charge
    {
        $this->initialisationFromShop($shop, $request);
        return $this->handle($charge);
    }


    public function htmlResponse(Charge $charge, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('Charge'),
                'breadcrumbs' => $this->getBreadcrumbs(
                        $charge,
                        $request->route()->getName(),
                        $request->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($charge, $request),
                    'next'     => $this->getNext($charge, $request),
                ],
                'pageHead' => [
                    'title'    => $charge->name,
                    'icon'     => [
                        'title' => __('Charge'),
                        'icon'  => 'fal fa-charging-station'
                    ],
                    'actions'  => [
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
                            'title'  => __('edit charge'),
                            'fields' => [
                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('name'),
                                    'value' => $charge->name
                                ],
                            ],
                        ]
                    ],

                    'args' => [
                        'updateRoute' => [
                            'name'       => 'grp.models.stock.update',
                            'parameters' => $charge->id

                        ],
                    ]
                ]
            ]
        );
    }

    public function getBreadcrumbs(Charge $charge, string $routeName, array $routeParameters): array
    {
        return ShowCharge::make()->getBreadcrumbs(
            charge: $charge,
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '(' . __('Editing') . ')'
        );
    }

    public function getPrevious(Charge $charge, ActionRequest $request): ?array
    {
        $previous = Charge::where('slug', '<', $charge->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Charge $charge, ActionRequest $request): ?array
    {
        $next = Charge::where('slug', '>', $charge->slug)->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }


    private function getNavigation(?Charge $charge, string $routeName): ?array
    {
        if (!$charge) {
            return null;
        }


        return match ($routeName) {
            'grp.org.shops.show.assets.charges.edit' => [
                'label' => $charge->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $charge->organisation->slug,
                        'shop'         => $charge->shop->slug,
                        'charge'       => $charge->slug
                    ]
                ]
            ],
        };
    }
}
