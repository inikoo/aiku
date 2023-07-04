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
                    'navigation'   => [
                        'previous' => $this->getPrevious($website, $request),
                        'next'     => $this->getNext($website, $request),
                    ],
                    'pageHead'    => [
                        'title'     => $website->name,
                        'icon'      => [
                            'title' => __('website'),
                            'icon'  => 'fal fa-globe'
                        ],
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
            'websites.edit' => [
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
