<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 11:40:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\OrgAction;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Exception;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditWebsite extends OrgAction
{
    public function handle(Website $website): Website
    {
        return $website;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");

    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, Website $website, ActionRequest $request): Website
    {
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($website);
    }

    /**
     * @throws Exception
     */
    public function htmlResponse(Website $website, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                    'title'       => __("Website's settings"),
                    'breadcrumbs' => $this->getBreadcrumbs(
                        $request->route()->getName(),
                        $request->route()->originalParameters()
                    ),
                    'navigation'   => [
                        'previous' => $this->getPrevious($website, $request),
                        'next'     => $this->getNext($website, $request),
                    ],
                    'pageHead'    => [
                        'title'     => __('Settings'),
                        'container' => [
                            'icon'    => ['fal', 'fa-globe'],
                            'tooltip' => __('Website'),
                            'label'   => Str::possessive($website->name)
                        ],

                        'iconRight'    =>
                            [
                                'icon'  => ['fal', 'sliders-h'],
                                'title' => __("Website's settings")
                            ],

                        'actions'   => [
                            [
                                'type'  => 'button',
                                'style' => 'exitEdit',
                                'label' => __('Exit settings'),
                                'route' => [
                                    'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                    'parameters' => array_values($request->route()->originalParameters())
                                ]
                            ]
                        ],
                    ],
                    'formData' => [
                        'blueprint' => [
                            [
                                'title'  => __('ID/domain'),
                                'icon'   => 'fa-light fa-id-card',
                                'fields' => [
                                    'code' => [
                                        'type'     => 'input',
                                        'label'    => __('code'),
                                        'value'    => $website->code,
                                        'required' => true,
                                    ],
                                    'name' => [
                                        'type'     => 'input',
                                        'label'    => __('name'),
                                        'value'    => $website->name,
                                        'required' => true,
                                    ],
                                    'domain' => [
                                        'type'      => 'inputWithAddOn',
                                        'label'     => __('domain'),
                                        'leftAddOn' => [
                                            'label'=> 'http://www.'
                                        ],
                                        'value'    => $website->domain,
                                        'required' => true,
                                    ],
                                ]
                            ],
                            [
                            'title'  => __('Registrations'),
                            'icon'   => 'fa-light fa-id-card',
                            'fields' => [
                                'approval' => [
                                    'type'     => 'toggle',
                                    'label'    => __('Registrations Approval'),
                                    'value'    => false,
                                    'required' => true,
                                ],
                                'registrations_type' => [
                                    'type'     => 'radio',
                                    'mode'     => 'card',
                                    'label'    => __('Registration Type'),
                                    'value'    => [
                                            'title'        => "type B",
                                            'description'  => 'This user able to create and delete',
                                            'label'        => '17 users left',
                                            'value'        => "typeB",
                                        ],
                                    'required' => true,
                                    'options'  => [
                                        [
                                            'title'        => "type A",
                                            'description'  => 'This user able to edit',
                                            'label'        => '425 users left',
                                            'value'        => "typeA",
                                        ],
                                        [
                                            'title'        => "type B",
                                            'description'  => 'This user able to create and delete',
                                            'label'        => '17 users left',
                                            'value'        => "typeB",
                                        ],
                                    ]
                                ],
                                'web_registrations' => [
                                    'type'     => 'webRegistrations',
                                    'label'    => __('Web Registration'),
                                    'value'    => [
                                        [
                                            'key'       => 'telephone',
                                            'name'      => __('telephone'),
                                            'show'      => true,
                                            'required'  => false,
                                        ],
                                        [
                                            'key'       => 'address',
                                            'name'      => __('address'),
                                            'show'      => false,
                                            'required'  => false,
                                        ],
                                        [
                                            'key'       => 'company',
                                            'name'      => __('company'),
                                            'show'      => false,
                                            'required'  => false,
                                        ],
                                        [
                                            'key'       => 'contact_name',
                                            'name'      => __('contact_name'),
                                            'show'      => false,
                                            'required'  => false,
                                        ],
                                        [
                                            'key'       => 'registration_number',
                                            'name'      => __('registration number'),
                                            'show'      => true,
                                            'required'  => false,
                                        ],
                                        [
                                            'key'       => 'tax_number',
                                            'name'      => __('tax number'),
                                            'show'      => false,
                                            'required'  => false,
                                        ],
                                        [
                                            'key'       => 'terms_and_conditions',
                                            'name'      => __('terms and conditions'),
                                            'show'      => true,
                                            'required'  => true,
                                        ],
                                        [
                                            'key'       => 'marketing',
                                            'name'      => __('marketing'),
                                            'show'      => false,
                                            'required'  => false,
                                        ],
                                ],
                                    'required' => true,
                                    'options'  => [
                                        [
                                            'key'       => 'telephone',
                                            'name'      => __('telephone'),
                                            'show'      => true,
                                            'required'  => false,
                                        ],
                                        [
                                            'key'       => 'address',
                                            'name'      => __('address'),
                                            'show'      => false,
                                            'required'  => false,
                                        ],
                                        [
                                            'key'       => 'company',
                                            'name'      => __('company'),
                                            'show'      => false,
                                            'required'  => false,
                                        ],
                                        [
                                            'key'       => 'contact_name',
                                            'name'      => __('contact name'),
                                            'show'      => false,
                                            'required'  => false,
                                        ],
                                        [
                                            'key'       => 'registration_number',
                                            'name'      => __('registration number'),
                                            'show'      => true,
                                            'required'  => false,
                                        ],
                                        [
                                            'key'       => 'tax_number',
                                            'name'      => __('tax number'),
                                            'show'      => false,
                                            'required'  => false,
                                        ],
                                        [
                                            'key'       => 'terms_and_conditions',
                                            'name'      => __('terms and conditions'),
                                            'show'      => true,
                                            'required'  => false,
                                        ],
                                        [
                                            'key'       => 'marketing',
                                            'name'      => __('marketing'),
                                            'show'      => false,
                                            'required'  => false,
                                        ],
                                ]
                                ]
                            ]
                        ],
                    ],
                        'args'      => [
                            'updateRoute' => [
                                'name'       => 'grp.models.website.update',
                                'parameters' => $website->id
                            ],
                        ]
                    ],

                ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowWebsite::make()->getBreadcrumbs(
            $routeName,
            $routeParameters,
            suffix: '('.__('editing').')'
        );
    }

    public function getPrevious(Website $website, ActionRequest $request): ?array
    {
        $previous = Website::where('code', '<', $website->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Website $website, ActionRequest $request): ?array
    {
        $next = Website::where('code', '>', $website->code)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Website $website, string $routeName): ?array
    {
        if (!$website) {
            return null;
        }

        return match ($routeName) {
            'grp.org.shops.show.web.websites.edit' => [
                'label' => $website->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'website' => $website->slug
                    ]
                ]
            ]
        };
    }
}
