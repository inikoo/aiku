<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:35:41 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage\UI;

use App\Actions\Helpers\Snapshot\UI\IndexSnapshots;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\UI\WithInertia;
use App\Actions\Web\HasWorkshopAction;
use App\Actions\Web\Website\UI\ShowWebsite;
use App\Enums\UI\Web\WebpageTabsEnum;
use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Http\Resources\Helpers\SnapshotResource;
use App\Http\Resources\Web\WebpageResource;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowWebpage extends OrgAction
{
    use AsAction;
    use WithInertia;
    use HasWorkshopAction;
    use HasWebAuthorisation;


    private Website $parent;


    public function asController(Organisation $organisation, Shop $shop, Website $website, Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->scope  = $shop;
        $this->parent = $website;
        $this->initialisationFromShop($shop, $request)->withTab(WebpageTabsEnum::values());

        return $webpage;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, Webpage $webpage, ActionRequest $request): Webpage
    {
        $this->scope  = $fulfilment;
        $this->parent = $website;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(WebpageTabsEnum::values());

        return $webpage;
    }


    public function htmlResponse(Webpage $webpage, ActionRequest $request): Response
    {
        $actions = $this->workshopActions($request);

        if ($webpage->purpose == WebpagePurposeEnum::BLOG) {
            $actions = array_merge(
                $actions,
                [
                    $this->canEdit ? [
                        'type'  => 'button',
                        'style' => 'create',
                        'label' => __('new article'),
                        'route' => [
                            'name'       => 'org.websites.show.blog.article.create',
                            'parameters' => [
                                'website' => $webpage->website->slug,
                            ]
                        ]
                    ] : []
                ]
            );
        } elseif ($webpage->type == WebpageTypeEnum::STOREFRONT) {
            $actions = array_merge(
                $actions,
                [
                    $this->canEdit ? [
                        'type'  => 'button',
                        'style' => 'create',
                        'label' => __('Main webpage'),
                        'route' => [
                            'name'       => 'org.websites.show.webpages.show.webpages.create',
                            'parameters' => [
                                'website' => $webpage->website->slug,
                                'webpage' => $webpage->slug

                            ]
                        ]
                    ] : []
                ]
            );
        } elseif (in_array(
            $webpage->type,
            [
                WebpageTypeEnum::SHOP,
                WebpageTypeEnum::CONTENT
            ]
        )) {
            $actions = array_merge(
                $actions,
                [
                    $this->canEdit ? [
                        'type'  => 'button',
                        'style' => 'create',
                        'label' => __('webpage'),
                        'route' => [
                            'name'       => 'org.websites.show.webpages.show.webpages.create',
                            'parameters' => [
                                'website' => $webpage->website->slug,
                                'webpage' => $webpage->slug
                            ]
                        ]
                    ] : []
                ]
            );
        }


        return Inertia::render(
            'Org/Web/Webpage',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('webpage'),
                'pageHead'    => [
                    'title'   => $webpage->code,
                    'icon'    => [
                        'title' => __('webpage'),
                        'icon'  => 'fal fa-browser'
                    ],
                    'actions' => $actions,
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => WebpageTabsEnum::navigation()
                ],

                WebpageTabsEnum::SHOWCASE->value => $this->tab == WebpageTabsEnum::SHOWCASE->value ?
                    fn() => WebpageResource::make($webpage)->getArray()
                    : Inertia::lazy(fn() => WebpageResource::make($webpage)->getArray()),

                WebpageTabsEnum::SNAPSHOTS->value => $this->tab == WebpageTabsEnum::SNAPSHOTS->value ?
                    fn() => SnapshotResource::collection(IndexSnapshots::run(parent: $webpage, prefix: 'snapshots'))
                    : Inertia::lazy(fn() => SnapshotResource::collection(IndexSnapshots::run(parent: $webpage, prefix: 'snapshots'))),

                WebpageTabsEnum::WEBPAGES->value => $this->tab == WebpageTabsEnum::WEBPAGES->value
                    ?
                    fn() => WebpageResource::collection(
                        IndexWebpages::run(
                            parent: $webpage,
                            prefix: 'webpages'
                        )
                    )
                    : Inertia::lazy(fn() => WebpageResource::collection(
                        IndexWebpages::run(
                            parent: $webpage,
                            prefix: 'webpages'
                        )
                    )),


                /*
                WebpageTabsEnum::CHANGELOG->value => $this->tab == WebpageTabsEnum::CHANGELOG->value ?
                    fn() => HistoryResource::collection(IndexHistories::run($webpage))
                    : Inertia::lazy(fn() => HistoryResource::collection(IndexHistories::run($webpage)))
                */


            ]
        )->table(
            IndexWebpages::make()->tableStructure(parent: $webpage, prefix: 'webpages')
        )->table(
            IndexSnapshots::make()->tableStructure(
                parent: $webpage,
                prefix: 'snapshots'
            )
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (Webpage $webpage, array $routeParameters, string $suffix) {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('webpages')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $webpage->code,
                        ],

                    ],
                    'suffix'         => $suffix

                ],
            ];
        };


        $webpage = Webpage::where('slug', $routeParameters['webpage'])->first();


        return
            match ($routeName) {
                'grp.org.shops.show.web.websites.show.webpages.show',
                'grp.org.shops.show.web.websites.show.webpages.edit',
                'grp.org.shops.show.web.websites.show.webpages.workshop'=>
                array_merge(
                    ShowWebsite::make()->getBreadcrumbs(
                        'grp.org.shops.show.web.websites.show',
                        Arr::only($routeParameters, ['organisation', 'shop', 'website'])
                    ),
                    $headCrumb(
                        $webpage,
                        [
                            'index' => [
                                'name'       => 'grp.org.shops.show.web.websites.show.webpages.index',
                                'parameters' => Arr::only($routeParameters, ['organisation', 'shop', 'website'])
                            ],
                            'model' => [
                                'name'       => 'grp.org.shops.show.web.websites.show.webpages.show',
                                'parameters' => Arr::only($routeParameters, ['organisation', 'shop', 'website', 'webpage'])
                            ]
                        ],
                        $suffix
                    ),
                ),

                'grp.org.fulfilments.show.web.websites.show.webpages.show',
                'grp.org.fulfilments.show.web.websites.show.webpages.edit',
                'grp.org.fulfilments.show.web.websites.show.webpages.workshop'=>
                array_merge(
                    ShowWebsite::make()->getBreadcrumbs(
                        'grp.org.fulfilments.show.web.websites.show',
                        Arr::only($routeParameters, ['organisation', 'fulfilment', 'website'])
                    ),
                    $headCrumb(
                        $webpage,
                        [
                            'index' => [
                                'name'       => 'grp.org.fulfilments.show.web.websites.show.webpages.index',
                                'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'website'])
                            ],
                            'model' => [
                                'name'       => 'grp.org.fulfilments.show.web.websites.show.webpages.show',
                                'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'website', 'webpage'])
                            ]
                        ],
                        $suffix
                    ),
                )
            };
    }
}
