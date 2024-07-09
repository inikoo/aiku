<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Banner\UI;

use App\Actions\InertiaAction;
use App\Enums\Web\Banner\BannerStateEnum;
use App\Models\Web\Banner;
use Exception;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditBanner extends InertiaAction
{
    public function handle(Banner $banner): Banner
    {
        return $banner;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->get('customerUser')->hasPermissionTo('portfolio.banners.edit');

        return $request->get('customerUser')->hasPermissionTo("portfolio.banners.edit");
    }

    public function asController( Banner $banner, ActionRequest $request): Banner
    {
        $this->initialisation($request);

        return $this->handle($banner);
    }

    /**
     * @throws Exception
     */
    public function htmlResponse(Banner $banner, ActionRequest $request): Response
    {
        $sections['properties'] = [
            'label'  => __('Banner properties'),
            'icon'   => 'fal fa-sliders-h',
            'fields' => [
                'name'                 => [
                    'type'     => 'input',
                    'label'    => __('name'),
                    'value'    => $banner->name,
                    'required' => true,
                ],

            ]
        ];


        if ($banner->state == BannerStateEnum::LIVE) {
            $sections['shutdown'] = [
                'label'  => __('Shutdown'),
                'icon'   => 'fal fa-power-off',
                'title'  => '',
                'fields' => [
                    'shutdown_action' => [
                        'type'   => 'action',
                        'action' => [
                            'type'  => 'button',
                            'style' => 'secondary',
                            'icon'  => ['fal', 'fa-power-off'],
                            'label' => __('Shutdown banner'),
                            'route' => [
                                'name'       => 'customer.models.banner.shutdown',
                                'parameters' => [
                                    'banner' => $banner->id
                                ]
                            ]
                        ],
                    ]
                ]
            ];
        }

        $sections['delete'] = [
            'label'  => __('Delete'),
            'icon'   => 'fal fa-trash-alt',
            'fields' => [
                'name' => [
                    'type'   => 'action',
                    'action' => [
                        'type'  => 'button',
                        'style' => 'delete',
                        'label' => __('delete banner'),
                        'method'=> 'delete',
                        'route' => [
                            'name'       => 'customer.models.banner.delete',
                            'parameters' => [
                                'banner' => $banner->id
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
                'title'       => __("Edit banner"),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($banner, $request),
                    'next'     => $this->getNext($banner, $request),
                ],
                'pageHead'    => [
                    'title'     => $banner->name,
                    'icon'      => [
                        'tooltip' => __('banner'),
                        'icon'    => 'fal fa-sign'
                    ],
                    'iconRight' => $banner->state->stateIcon()[$banner->state->value],
                    'actions'   => [
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
                'formData'    => [
                    'current'   => $currentSection,
                    'blueprint' => $sections,
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'customer.models.banner.update',
                            'parameters' => $banner->id
                        ],
                    ]
                ],

            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowBanner::make()->getBreadcrumbs(
            $routeName,
            $routeParameters,
            suffix: '('.__('editing').')'
        );
    }

    public function getPrevious(Banner $banner, ActionRequest $request): ?array
    {
        $previous = Banner::where('slug', '<', $banner->slug)->orderBy('slug', 'desc')->first();

        return $this->getNavigation($previous, $request);
    }

    public function getNext(Banner $banner, ActionRequest $request): ?array
    {
        $next = Banner::where('slug', '>', $banner->slug)->orderBy('slug')->first();

        return $this->getNavigation($next, $request);
    }

    private function getNavigation(?Banner $banner, ActionRequest $request): ?array
    {
        if (!$banner) {
            return null;
        }

        $routeName = $request->route()->getName();

        return match ($routeName) {
            'customer.banners.banners.edit' => [
                'label' => $banner->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => $request->route()->originalParameters()
                ]
            ],
            default => null
        };
    }
}
