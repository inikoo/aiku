<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Workplace\UI;

use App\Actions\Assets\Country\UI\GetAddressData;
use App\Actions\OrgAction;
use App\Enums\HumanResources\Workplace\WorkplaceTypeEnum;
use App\Http\Resources\Helpers\AddressFormFieldsResource;
use App\Models\Helpers\Address;
use App\Models\SysAdmin\Organisation;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateWorkplace extends OrgAction
{
    /**
     * @throws Exception
     */
    public function handle(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('new workplace'),
                'pageHead'    => [
                    'title'   => __('new workplace'),
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'grp.org.hr.workplaces.index',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('work place'),
                            'fields' => [
                                'name' => [
                                    'type'        => 'input',
                                    'label'       => __('name'),
                                    'placeholder' => __(''),
                                    'required'    => true
                                ],
                                'type' => [
                                    'type'        => 'select',
                                    'label'       => __('type'),
                                    'options'     => Options::forEnum(WorkplaceTypeEnum::class),
                                    'placeholder' => __('Select a type'),
                                    'mode'        => 'single',
                                    'required'    => true,
                                    'searchable'  => true,
                                ],
                                'address' => [
                                    'type'  => 'address',
                                    'label' => __('Address'),
                                    'value' => AddressFormFieldsResource::make(
                                        new Address(
                                            [
                                                'country_id' => $this->organisation->country_id,

                                            ]
                                        )
                                    )->getArray(),
                                    'options' => [
                                        'countriesAddressData' => GetAddressData::run()

                                    ],
                                    'required' => true,
                                ]
                            ]
                        ]

                    ],
                    'route' => [
                        'name'       => 'grp.models.org.workplace.store',
                        'parameters' => [$this->organisation->id]
                    ]

                ],


            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }


    /**
     * @throws Exception
     */
    public function asController(Organisation $organisation, ActionRequest $request): Response
    {
        $this->initialisation($organisation, $request);

        return $this->handle($request);
    }


    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            IndexWorkplaces::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating workplace'),
                    ]
                ]
            ]
        );
    }

}
