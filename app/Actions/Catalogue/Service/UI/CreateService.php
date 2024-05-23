<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 May 2024 09:45:43 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Service\UI;

use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentRentals;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\Rental\RentalUnitEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateService extends OrgAction
{
    /**
     * @throws Exception
     */
    public function handle(Fulfilment $fulfilment, ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'    => __('new rental'),
                'pageHead' => [
                    'title' => __('new rental')
                ],
                'formData' => [
                    'fullLayout' => true,
                    'blueprint'  =>
                        [
                            [
                                'title'  => __('name'),
                                'fields' => [
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

    /**
     * @throws Exception
     */
    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): Response
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment, $request);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            IndexFulfilmentRentals::make()->getBreadcrumbs(
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating rental'),
                    ]
                ]
            ]
        );
    }

}
