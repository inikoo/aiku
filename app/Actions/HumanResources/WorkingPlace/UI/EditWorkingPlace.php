<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:14:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\WorkingPlace\UI;

use App\Actions\Assets\Country\UI\GetAddressData;
use App\Actions\InertiaAction;
use App\Enums\HumanResources\Workplace\WorkplaceTypeEnum;
use App\Http\Resources\Helpers\AddressFormFieldsResource;
use App\Models\Helpers\Address;
use App\Models\HumanResources\Workplace;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditWorkingPlace extends InertiaAction
{
    public function handle(Workplace $workplace): Workplace
    {
        return $workplace;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("hr.edit");
    }

    public function asController(Workplace $workplace, ActionRequest $request): Workplace
    {
        $this->initialisation($request);

        return $this->handle($workplace);
    }


    /**
     * @throws Exception
     */
    public function htmlResponse(Workplace $workplace, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('working place'),
                'breadcrumbs' => $this->getBreadcrumbs($workplace),
                'pageHead'    => [
                    'title'    => $workplace->name,
                    'actions'  => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ]
                    ]
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('edit working place'),
                            'fields' => [
                                'name' => [
                                    'type'          => 'input',
                                    'label'         => __('name'),
                                    'placeholder'   => __('Input your name'),
                                    'value'         => $workplace->name,
                                    'required'      => true
                                ],
                                'type' => [
                                    'type'        => 'select',
                                    'label'       => __('type'),
                                    'options'     => Options::forEnum(WorkplaceTypeEnum::class),
                                    'placeholder' => __('Select a type'),
                                    'mode'        => 'single',
                                    'value'       => $workplace->type,
                                    'required'    => true,
                                    'searchable'  => true
                                ],
                                'address'      => [
                                    'type'    => 'address',
                                    'label'   => __('Address'),
                                    'value'   => AddressFormFieldsResource::make(
                                        new Address(
                                            [
                                                'country_id' => app('currentTenant')->country_id,

                                            ]
                                        )
                                    )->getArray(),
                                    'options' => [
                                        'countriesAddressData' => GetAddressData::run()

                                    ],
                                    'required'    => true
                                ]
                            ]
                        ],

                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.working-place.update',
                            'parameters' => $workplace->slug

                        ],
                    ]

                ],

            ]
        );
    }

    public function getBreadcrumbs(Workplace $workplace): array
    {
        return ShowWorkingPlace::make()->getBreadcrumbs(workplace: $workplace, suffix: '('.__('editing').')');
    }
}
