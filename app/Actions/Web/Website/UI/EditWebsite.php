<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 11:40:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\InertiaAction;
use App\Models\Web\Website;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditWebsite extends InertiaAction
{
    public function handle(Website $website): Website
    {
        return $website;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('websites.edit');
        return $request->user()->hasPermissionTo("websites.edit");

    }

    public function asController(Website $website, ActionRequest $request): Website
    {
        $this->initialisation($request);
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
                'title'       => __('edit website'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters()
                ),
                'pageHead'    => [
                    'title'     => $website->name,
                    'actions'   => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                                'parameters' => array_values($this->originalParameters)
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
                                            'name'      => __('telephone'),
                                            'show'      => true,
                                            'required'  => false,
                                        ],
                                        [
                                            'name'      => __('address'),
                                            'show'      => false,
                                            'required'  => false,
                                        ],
                                        [
                                            'name'      => __('username'),
                                            'show'      => true,
                                            'required'  => true,
                                        ],
                                        [
                                            'name'      => __('password'),
                                            'show'      => true,
                                            'required'  => true,
                                        ],
                                ],
                                    'required' => true,
                                    'options'  => [
                                            [
                                                'name'      => __('telephone'),
                                                'show'      => true,
                                                'required'  => false,
                                            ],
                                            [
                                                'name'      => __('address'),
                                                'show'      => false,
                                                'required'  => false,
                                            ],
                                            [
                                                'name'      => __('username'),
                                                'show'      => true,
                                                'required'  => true,
                                            ],
                                            [
                                                'name'      => __('password'),
                                                'show'      => true,
                                                'required'  => true,
                                            ],
                                    ]
                                ]
                            ]
                        ],
                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'models.website.update',
                            'parameters' => $website->slug
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
}
