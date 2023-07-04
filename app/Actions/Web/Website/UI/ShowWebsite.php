<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 29 May 2023 12:18:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\Helpers\History\IndexHistories;
use App\Actions\InertiaAction;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\UI\Dashboard\Dashboard;
use App\Actions\UI\WithInertia;
use App\Actions\Web\WebpageVariant\IndexWebpageVariants;
use App\Enums\UI\WebsiteTabsEnum;
use App\Http\Resources\Market\WebpageResource;
use App\Http\Resources\Market\WebsiteResource;
use App\Http\Resources\SysAdmin\HistoryResource;
use App\Models\Market\Shop;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowWebsite extends InertiaAction
{
    use AsAction;
    use WithInertia;


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->can('websites.edit');
        $this->canDelete = $request->user()->can('websites.edit');
        return $request->user()->hasPermissionTo("shops.websites.view");
    }

    public function asController(Website $website, ActionRequest $request): Website
    {
        $this->initialisation($request)->withTab(WebsiteTabsEnum::values());

        return $website;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, Website $website, ActionRequest $request): Website
    {
        $this->initialisation($request)->withTab(WebsiteTabsEnum::values());

        return $website;
    }

    public function htmlResponse(Website $website, ActionRequest $request): Response
    {
        $this->validateAttributes();

        return Inertia::render(
            'Web/Website',
            [
                'title'       => __('Website'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'navigation'   => [
                    'previous' => $this->getPrevious($website, $request),
                    'next'     => $this->getNext($website, $request),
                ],
                'pageHead'    => [
                    'title'   => $website->name,
                    'icon'    => [
                        'title' => __('website'),
                        'icon'  => 'fal fa-globe'
                    ],
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'label' => __('settings'),
                            'icon'  => ["fal", "fa-sliders-h"],
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : false,
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'label' => __('workshop'),
                            'icon'  => ["fal", "fa-drafting-compass"],
                            'route' => [
                                'name'       => preg_replace('/show$/', 'workshop', $this->routeName),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : false,
                        /*$this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'websites.remove',
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : false */
                    ],

                ],
                'tabs'                                => [
                    'current'    => $this->tab,
                    'navigation' => WebsiteTabsEnum::navigation()
                ],
                WebsiteTabsEnum::WEBPAGES->value => $this->tab == WebsiteTabsEnum::WEBPAGES->value
                    ?
                    fn () => WebpageResource::collection(
                        IndexWebpageVariants::run($website)
                    )
                    : Inertia::lazy(fn () => WebpageResource::collection(
                        IndexWebpageVariants::run($website)
                    )),

                WebsiteTabsEnum::CHANGELOG->value => $this->tab == WebsiteTabsEnum::CHANGELOG->value ?
                    fn () => HistoryResource::collection(IndexHistories::run($website))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistories::run($website)))
            ]
        )->table(
            IndexWebpageVariants::make()->tableStructure(
                $website
            )
        )->table(IndexHistories::make()->tableStructure());
    }


    public function jsonResponse(Website $website): WebsiteResource
    {
        return new WebsiteResource($website);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (string $type, Website $website, array $routeParameters, string $suffix) {
            return [
                [

                    'type'           => $type,
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('websites')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $website->name,
                        ],

                    ],
                    'simple'=> [
                        'route' => $routeParameters['model'],
                        'label' => $website->name
                    ],


                    'suffix'=> $suffix

                ],
            ];
        };



        return match ($routeName) {
            'websites.show',
            'websites.edit' =>

            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    'modelWithIndex',
                    $routeParameters['website'],
                    [
                        'index' => [
                            'name'       => 'websites.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'websites.show',
                            'parameters' => [$routeParameters['website']->slug]
                        ]
                    ],
                    $suffix
                ),
            ),


            'shops.show.websites.show',
            'shops.show.websites.edit'
            => array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    'simple',
                    $routeParameters['website'],
                    [
                        'index' => [
                            'name'       => 'shops.show.websites.index',
                            'parameters' => [
                                $routeParameters['shop']->slug,
                            ]
                        ],
                        'model' => [
                            'name'       => 'shops.show.websites.show',
                            'parameters' => [
                                $routeParameters['shop']->slug,
                                $routeParameters['website']->slug
                            ]
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
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
            'websites.show' => [
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
