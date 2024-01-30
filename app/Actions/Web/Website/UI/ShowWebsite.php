<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 29 May 2023 12:18:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\CRM\WebUser\IndexWebUser;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Actions\Web\WebpageVariant\IndexWebpageVariants;
use App\Enums\UI\WebsiteTabsEnum;
use App\Http\Resources\CRM\WebUserResource;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Web\WebpageResource;
use App\Http\Resources\Web\WebsiteResource;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowWebsite extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("web.{$this->shop->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("web.{$this->shop->id}.edit");

        return $request->user()->hasPermissionTo("web.{$this->shop->id}.view");
    }

    public function asController(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): Website
    {
        $this->initialisationFromShop($shop, $request)->withTab(WebsiteTabsEnum::values());

        return $website;
    }


    public function htmlResponse(Website $website, ActionRequest $request): Response
    {
        $this->validateAttributes();
        /*
                dd( $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ));
        */

        return Inertia::render(
            'Org/Web/Website',
            [
                'title'                          => __('Website'),
                'breadcrumbs'                    => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'                     => [
                    'previous' => $this->getPrevious($website, $request),
                    'next'     => $this->getNext($website, $request),
                ],
                'pageHead'                       => [
                    'title'   => $website->name,
                    'icon'    => [
                        'title' => __('website'),
                        'icon'  => 'fal fa-globe'
                    ],
                    'actionsx' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'label' => __('settings'),
                            'icon'  => ["fal", "fa-sliders-h"],
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'label' => __('workshop'),
                            'icon'  => ["fal", "fa-drafting-compass"],
                            'route' => [
                                'name'       => preg_replace('/show$/', 'workshop', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                        /*$this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'websites.remove',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false */
                    ],

                ],
                'tabs'                           => [
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

                WebsiteTabsEnum::USERS->value     => $this->tab == WebsiteTabsEnum::USERS->value
                    ?
                    WebUserResource::collection(
                        IndexWebUser::run(
                            parent: $website,
                            prefix: 'web_users'
                        )
                    )
                    : Inertia::lazy(fn () => WebUserResource::collection(
                        IndexWebUser::run(
                            parent: $website,
                            prefix: 'web_users'
                        )
                    )),
                WebsiteTabsEnum::CHANGELOG->value => $this->tab == WebsiteTabsEnum::CHANGELOG->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($website))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($website)))
            ]
        )->table(
            IndexWebpageVariants::make()->tableStructure(
                $website
            )
        )->table(
            IndexWebUser::make()->tableStructure(
                modelOperations: [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'grp.org.shop.show.websites.show.web-users.create',
                            'parameters' => array_values($request->route()->originalParameters())
                        ],
                        'label' => __('users')
                    ] : false,
                ],
                prefix: 'web_users'
            )
        )->table(IndexHistory::make()->tableStructure());
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
                    'simple'         => [
                        'route' => $routeParameters['model'],
                        'label' => $website->name
                    ],
                    'suffix' => $suffix

                ],
            ];
        };






        return match ($routeName) {
            'grp.org.shops.show.websites.show',
            'grp.org.shops.show.websites.edit' =>

            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    'modelWithIndex',
                    Website::where('slug', $routeParameters['website'])->first(),
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.websites.index',
                            'parameters' => Arr::only($routeParameters, ['organisation','shop'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.websites.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                ),
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
            'grp.org.shops.show.websites.show' => [
                'label' => $website->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $website->shop->organisation->slug,
                        'shop'         => $website->shop->slug,
                        'website'      => $website->slug
                    ]
                ]
            ]
        };
    }
}
