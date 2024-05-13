<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect\Tags\UI;

use App\Actions\CRM\Prospect\UI\IndexProspects;
use App\Actions\InertiaAction;
use App\Models\Helpers\Tag;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditProspectTag extends InertiaAction
{
    public function handle(Tag $tag): Tag
    {
        return $tag;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("crm.prospects.edit");
    }

    public function inShop(Shop $shop, Tag $tag, ActionRequest $request): Tag
    {
        $this->initialisation($request);
        return $this->handle($tag);
    }

    public function htmlResponse(Tag $tag, ActionRequest $request): Response
    {
        $sections['properties'] = [
            'label'  => __('tag properties'),
            'icon'   => 'fal fa-sliders-h',
            'fields' => [
                'label' => [
                    'type'     => 'input',
                    'label'    => __('label'),
                    'required' => true,
                    'value'    => $tag->label
                ],
            ]
        ];

        $sections['delete'] = [
            'label'  => __('Delete'),
            'icon'   => 'fal fa-trash-alt',
            'fields' => [
                'name' => [
                    'type'   => 'action',
                    'action' => [
                        'type'   => 'button',
                        'style'  => 'delete',
                        'label'  => __('delete customer'),
                        'method' => 'delete',
                        'route'  => [
                            'name'       => 'org.models.prospect.tag.delete',
                            'parameters' => array_values($request->route()->originalParameters())
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
                'title'       => __("Tag"),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation' => [
                    'previous' => $this->getPrevious($tag, $request),
                    'next'     => $this->getNext($tag, $request),
                ],
                'pageHead' => [
                    'title' => $tag->name,
                    'icon'  => [
                        'title' => __('query'),
                        'icon'  => 'fal fa-globe'
                    ],
                    'iconRight' =>
                        [
                            'icon'  => ['fal', 'fa-edit'],
                            'title' => __("Editing tag")
                        ],

                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'exit',
                            'label' => __('Exit edit'),
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ],
                ],
                'formData' => [
                    'current'   => $currentSection,
                    'blueprint' => $sections,
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'org.models.shop.prospect.tag.update',
                            'parameters' => array_values($request->route()->originalParameters())
                        ],
                    ]
                ],

            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexProspects::make()->getBreadcrumbs(
                routeName: preg_replace('/create$/', 'index', $routeName),
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating tag'),
                    ]
                ]
            ]
        );
    }

    public function getPrevious(Tag $tag, ActionRequest $request): ?array
    {
        $previous = Tag::where('id', '<', $tag->id)->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Tag $tag, ActionRequest $request): ?array
    {
        $next = Tag::where('id', '>', $tag->id)->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Tag $tag, string $routeName): ?array
    {
        if (!$tag) {
            return null;
        }

        return match ($routeName) {
            'grp.org.shops.show.crm.prospects.tags.edit' => [
                'label' => $tag->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => $request->route()->originalParameters()
                ]
            ]
        };
    }
}
