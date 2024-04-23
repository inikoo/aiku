<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:35:41 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage\UI;

use App\Actions\OrgAction;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditWebpage extends OrgAction
{
    public function handle(Webpage $webpage): Webpage
    {
        return $webpage;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->initialisation($organisation, $request);

        return $this->handle($webpage);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWebsite(Organisation $organisation, Fulfilment $fulfilment, Website $website, Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($webpage);
    }

    /**
     * @throws Exception
     */
    public function htmlResponse(Webpage $webpage, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __("Webpage's settings"),
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()),

                'pageHead' => [
                    'title' => __('webpage settings'),


                    'iconRight' =>
                        [
                            'icon'  => ['fal', 'sliders-h'],
                            'title' => __("Webpage settings")
                        ],

                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'exit',
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
                            'title'  => 'state',
                            'icon'   => 'fal fa-signal-stream',
                            'fields' => [
                                'launch' => [
                                    'type'     => 'action',
                                    'label'    => __('Launch'),
                                    'icon'     => 'fal fa-rocket-launch',
                                    'value'    => false,
                                    'required' => true,
                                    'button'   => [
                                        'method' => 'patch',
                                        'data'   => [
                                            'state' => 'live'
                                        ],
                                        'route'  => [
                                            'name' => 'org.models.webpage.state.update'
                                        ]
                                    ]
                                ],
                            ]
                        ],
                        [
                            'title'  => 'state',
                            'icon'   => 'fal fa-signal-stream',
                            'fields' => [
                                'maintenance' => [
                                    'type'     => 'action',
                                    'label'    => __('Maintenance'),
                                    'icon'     => 'fal fa-rocket-launch',
                                    'value'    => false,
                                    'required' => true,
                                    'button'   => [
                                        'style'  => 'negative',
                                        'method' => 'patch',
                                        'data'   => [
                                            'state'  => 'live',
                                            'status' => false
                                        ],
                                        'route'  => [
                                            'name' => 'org.models.webpage.state.update'
                                        ]
                                    ]
                                ],
                            ]
                        ],
                        [
                            'title'  => 'state',
                            'icon'   => 'fal fa-signal-stream',
                            'fields' => [
                                'restore' => [
                                    'type'     => 'action',
                                    'label'    => __('Restore'),
                                    'icon'     => 'fal fa-rocket-launch',
                                    'value'    => false,
                                    'required' => true,
                                    'button'   => [
                                        'style'  => 'primary',
                                        'method' => 'patch',
                                        'data'   => [
                                            'state'  => 'live',
                                            'status' => true
                                        ],
                                        'route'  => [
                                            'name' => 'org.models.webpage.state.update'
                                        ]
                                    ]
                                ],
                            ]
                        ],
                        [
                            'title'  => 'state',
                            'icon'   => 'fal fa-signal-stream',
                            'fields' => [
                                'closed' => [
                                    'type'     => 'action',
                                    'label'    => __('Closed'),
                                    // 'icon'     => 'fal fa-rocket-launch',
                                    'value'    => false,
                                    'required' => true,
                                    'button'   => [
                                        'style'  => 'negative',
                                        'method' => 'patch',
                                        'data'   => [
                                            'state' => 'closed',
                                        ],
                                        'route'  => [
                                            'name' => 'org.models.webpage.state.update'
                                        ]
                                    ]
                                ],
                            ]
                        ],
                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'org.models.webpage.update',
                            'parameters' => $webpage->slug
                        ],
                    ]
                ],

            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowWebpage::make()->getBreadcrumbs(
            $routeName,
            $routeParameters,
            suffix: '('.__('settings').')'
        );
    }


}
