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
    public function htmlResponse(Website $website): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('edit website'),
                'breadcrumbs' => $this->getBreadcrumbs($website),
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
                            'title'  => __('Detail'),
                            'icon'   => 'fa-light fa-id-card',
                            'fields' => [
                                'domain' => [
                                    'type'      => 'inputWithAddOn',
                                    'label'     => __('domain'),
                                    'leftAddOn' => [
                                        'label'=> 'http://www.'
                                    ],
                                    'value'    => $website->domain
                                ],
                            ]
                        ],
                        [
                            'title'  => __('ID/name'),
                            'fields' => [
                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $website->code
                                ],
                                'name' => [
                                    'type'     => 'input',
                                    'label'    => __('name'),
                                    'value'    => $website->name
                                ],
                            ]
                        ],
                    ]
                ],
                'args'      => [
                    'updateRoute' => [
                        'name'       => 'models.website.update',
                        'parameters' => $website->slug
                    ],
                ]
            ]
        );
    }


    public function getBreadcrumbs(Website $website): array
    {
        $routeParameters = ['website' => $website];
        return ShowWebsite::make()->getBreadcrumbs($website, $routeParameters, suffix: '('.__('editing').')');
    }
}
