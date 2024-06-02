<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 09:52:43 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product\UI;

use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentPhysicalGoods;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Fulfilment\Rental\RentalUnitEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreatePhysicalGoods extends OrgAction
{
    public function handle(Fulfilment $fulfilment, ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'    => __('new goods'),
                'pageHead' => [
                    'title' => __('new goods')
                ],
                'formData' => [
                    'fullLayout' => true,
                    'blueprint'  =>
                        [
                            [
                                'title'  => __('Create Goods'),
                                'fields' => [
                                    'code' => [
                                        'type'       => 'input',
                                        'label'      => __('code'),
                                        'required'   => true
                                    ],
                                    'name' => [
                                        'type'       => 'input',
                                        'label'      => __('name'),
                                        'required'   => true
                                    ],
                                    'price' => [
                                        'type'       => 'input',
                                        'label'      => __('price'),
                                        'required'   => true
                                    ],
                                    'unit' => [
                                        'type'     => 'select',
                                        'label'    => __('unit'),
                                        'required' => true,
                                        'options'  => Options::forEnum(RentalUnitEnum::class)
                                    ],
                                    'state' => [
                                        'type'     => 'select',
                                        'label'    => __('state'),
                                        'required' => true,
                                        'options'  => Options::forEnum(ProductStateEnum::class)
                                    ]
                                ]
                            ]
                        ],
                    'route' => [
                        'name'       => 'grp.models.org.fulfilment.rentals.store',
                        'parameters' => [
                            'organisation' => $fulfilment->organisation_id,
                            'fulfilment'   => $fulfilment->id,
                        ]
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): Response
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment, $request);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            IndexFulfilmentPhysicalGoods::make()->getBreadcrumbs(
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('Creating goods'),
                    ]
                ]
            ]
        );
    }

}
