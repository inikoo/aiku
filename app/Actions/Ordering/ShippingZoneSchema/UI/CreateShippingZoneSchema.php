<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 12:47:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\ShippingZoneSchema\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Enums\Ordering\ShippingZoneSchema\ShippingZoneSchemaTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateShippingZoneSchema extends OrgAction
{
    use HasCatalogueAuthorisation;

    public function handle(Shop $shop, ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('new schema'),
                'pageHead'    => [
                    'title'        => __('new schema'),
                    'icon'         => [
                        'icon'  => ['fal', 'fa-shipping-fast'],
                        'title' => __('Shipping Zone Schema')
                    ],
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => preg_replace('/create$/', 'index', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                        ]
                    ]
                ],
                'formData'    => [
                    'blueprint' =>
                        [
                            [
                                'title'  => __('contact'),
                                'fields' => [
                                    'name' => [
                                        'type'  => 'input',
                                        'label' => __('name')
                                    ],
                                    'type' => [
                                        'type'        => 'select',
                                        'label'       => __('type'),
                                        'options'     => Options::forEnum(ShippingZoneSchemaTypeEnum::class),
                                        'placeholder' => __('Select a type'),
                                        'mode'        => 'single',
                                        'required'    => true,
                                    ],
                                ]
                            ]
                        ],
                    'route'     => [
                        'name'      => 'grp.models.org.shop.customer.store',
                        'parameters'=> [
                            'organisation' => $shop->organisation_id,
                            'shop'         => $shop->id
                            ]
                    ]
                ]

            ]
        );
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Response
    {
        $this->initialisationFromShop($shop, $request);
        return $this->handle($shop, $request);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexShippingZoneSchemas::make()->getBreadcrumbs(
                routeName: preg_replace('/create$/', 'index', $routeName),
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('Creating schema'),
                    ]
                ]
            ]
        );
    }
}
