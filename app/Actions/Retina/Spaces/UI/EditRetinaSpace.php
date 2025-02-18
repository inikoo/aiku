<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 31 Jan 2025 14:28:27 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Spaces\UI;

use App\Actions\Fulfilment\UI\WithFulfilmentAuthorisation;
use App\Actions\OrgAction;
use App\Enums\Billables\Rental\RentalTypeEnum;
use App\Models\Billables\Rental;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Space;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditRetinaSpace extends OrgAction
{
    use WithFulfilmentAuthorisation;

    public function handle(Space $space, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('edit space'),
                'pageHead'    => [
                    'title'   => __('edit space'),
                    'icon'    => [
                        'icon'  => ['fal', 'fa-user'],
                        'title' => __('space')
                    ],
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'index', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                        ]
                    ]
                ],
                'formData'    => [
                    'blueprint' =>
                        [
                            [
                                'title'  => __('information'),
                                'fields' => [
                                    'reference' => [
                                        'type'     => 'input',
                                        'required' => true,
                                        'value' => $space->reference,
                                        'label'    => __('reference')
                                    ],

                                    'start_at'        => [
                                        'type'     => 'date',
                                        'required' => true,
                                        'value' => $space->start_at,
                                        'label'    => __('start rent at')
                                    ],
                                    'end_at'          => [
                                        'type'  => 'date',
                                        'value' => $space->end_at,
                                        'label' => __('end rent at')
                                    ],
                                    'rental_id'       => [
                                        'type'     => 'select',
                                        'required' => true,
                                        'label'    => __('rental'),
                                        'value' => $space->rental_id,
                                        'options'  => Options::forModels(Rental::where('type', RentalTypeEnum::SPACE->value))
                                    ],
                                    'exclude_weekend' => [
                                        'type'     => 'toggle',
                                        'required' => true,
                                        'value'    => $space->exclude_weekend,
                                        'label'    => __('exclude weekend')
                                    ],
                                ]
                            ]
                        ],
                        'args' => [
                            'updateRoute'     => [
                                'name'       => 'grp.models.fulfilment_customer_space.update',
                                'parameters' => [
                                    'fulfilmentCustomer' => $space->fulfilment_customer_id,
                                    'space' => $space->id
                                ]
                            ]
                        ]
                ]
            ]
        );
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, Space $space, ActionRequest $request): Response
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($space, $request);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            ShowSpace::make()->getBreadcrumbs(
                routeName: $routeName,
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('Editing space'),
                    ]
                ]
            ]
        );
    }
}
