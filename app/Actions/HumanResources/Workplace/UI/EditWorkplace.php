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
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditWorkplace extends OrgAction
{
    public function handle(Workplace $workplace): Workplace
    {
        return $workplace;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function asController(Organisation $organisation, Workplace $workplace, ActionRequest $request): Workplace
    {
        $this->initialisation($organisation, $request);

        return $this->handle($workplace);
    }


    /**
     * @throws Exception
     */
    public function htmlResponse(Workplace $workplace, ActionRequest $request): Response
    {

        $sections['properties'] = [
            'label'  => __('Properties'),
            'icon'   => 'fal fa-key',
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
                                'country_id' => $workplace->organisation->country_id,

                            ]
                        )
                    )->getArray(),
                    'options' => [
                        'countriesAddressData' => GetAddressData::run()

                    ],
                    'required'    => true
                ]
            ]
        ];

        $sections['delete'] = [
            'label'  => __('Delete'),
            'icon'   => 'fal fa-trash-alt',
            'fields' => [
                'name' => [
                    'type'   => 'action',
                    'action' => [
                        'type'  => 'button',
                        'style' => 'delete',
                        'label' => __('delete workplace'),
                        'route' => [
                            'name'       => 'customer.models.banner.delete',
                            'parameters' => [
                                'banner' => $workplace->id
                            ]
                        ]
                    ],
                ]
            ]
        ];

        $currentSection = 'properties';
        if ($request->has('section') and Arr::has($sections, $request->get('section'))) {
            $currentSection = $request->get('section');
        }

        return Inertia::render(
            'EditModel',
            [
                'title'                            => __('editing working place'),
                'breadcrumbs'                      => $this->getBreadcrumbs($request->route()->originalParameters()),
                'navigation'                       => [
                    'previous' => $this->getPrevious($workplace, $request),
                    'next'     => $this->getNext($workplace, $request),
                ],
                'pageHead'    => [
                    'title'    => $workplace->name,
                    'icon'     =>
                        [
                            'icon'  => ['fal', 'building'],
                            'title' => __('working place')
                        ],
                    'actions'  => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ]
                ],

                'formData' => [
                    'current'   => $currentSection,
                    'blueprint' => $sections,
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.org.models.workplace.update',
                            'parameters' => $request->route()->originalParameters()
                        ],
                    ]
                ],
            ]
        );
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return ShowWorkplace::make()->getBreadcrumbs(routeParameters: $routeParameters, suffix: '('.__('editing').')');
    }

    public function getPrevious(Workplace $workplace, ActionRequest $request): ?array
    {
        $previous = Workplace::where('slug', '<', $workplace->slug)->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Workplace $workplace, ActionRequest $request): ?array
    {
        $next = Workplace::where('slug', '>', $workplace->slug)->orderBy('slug')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Workplace $workplace, string $routeName): ?array
    {
        if (!$workplace) {
            return null;
        }

        return match ($routeName) {
            'grp.org.hr.workplaces.show' => [
                'label' => $workplace->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'workplace'    => $workplace->slug
                    ]
                ]
            ]
        };
    }
}
