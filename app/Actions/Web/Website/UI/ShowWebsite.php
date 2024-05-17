<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 29 May 2023 12:18:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\CRM\WebUser\IndexWebUsers;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\Web\HasWorkshopAction;
use App\Enums\UI\Web\WebsiteTabsEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Http\Resources\CRM\WebUsersResource;
use App\Http\Resources\History\HistoryResource;
use App\Http\Resources\Web\WebsiteResource;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowWebsite extends OrgAction
{
    use HasWorkshopAction;

    private Fulfilment|Shop|Organisation $parent;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Shop) {
            $this->canEdit      = $request->user()->hasPermissionTo("web.{$this->shop->id}.edit");
            $this->isSupervisor = $request->user()->hasPermissionTo("supervisor-web.{$this->shop->id}");

            return $request->user()->hasPermissionTo("web.{$this->shop->id}.view");
        } elseif ($this->parent instanceof Fulfilment) {
            $this->canEdit      = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
            $this->isSupervisor = $request->user()->hasPermissionTo("supervisor-fulfilment-shop.{$this->fulfilment->id}");

            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
        }

        return false;
    }

    public function asController(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): Website
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(WebsiteTabsEnum::values());

        return $website;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, ActionRequest $request): Website
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(WebsiteTabsEnum::values());

        return $website;
    }


    public function htmlResponse(Website $website, ActionRequest $request): Response
    {


        return Inertia::render(
            'Org/Web/Website',
            [
                'title'       => __('Website'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => $this->parent instanceof Organisation ? [
                    'previous' => $this->getPrevious($website, $request),
                    'next'     => $this->getNext($website, $request),
                ] : null,
                'pageHead'    => [
                    'title'     => $website->name,
                    'icon'      => [
                        'title' => __('website'),
                        'icon'  => 'fal fa-globe'
                    ],
                    'iconRight' => $website->state->stateIcon()[$website->state->value],
                    'actions'   =>

                        array_merge(
                            $this->workshopActions($request),
                            [
                                $this->isSupervisor && $website->state==WebsiteStateEnum::IN_PROCESS ? [
                                    'type'  => 'button',
                                    'style' => 'edit',
                                    'label' => __('launch'),
                                    'icon'  => ["fal", "fa-rocket"],
                                    'route' => [
                                        'method'     => 'post',
                                        'name'       => 'grp.models.website.launch',
                                        'parameters' => $website->id
                                    ]
                                ] : [],
                            ]
                        ),

                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => WebsiteTabsEnum::navigation()
                ],

                WebsiteTabsEnum::SHOWCASE->value => $this->tab == WebsiteTabsEnum::SHOWCASE->value ? WebsiteResource::make($website)->getArray() : Inertia::lazy(fn () => WebsiteResource::make($website)->getArray()),

                WebsiteTabsEnum::WEB_USERS->value     => $this->tab == WebsiteTabsEnum::WEB_USERS->value
                    ?
                    WebUsersResource::collection(
                        IndexWebUsers::run(
                            parent: $website,
                            prefix: 'web_users'
                        )
                    )
                    : Inertia::lazy(fn () => WebUsersResource::collection(
                        IndexWebUsers::run(
                            parent: $website,
                            prefix: 'web_users'
                        )
                    )),
                WebsiteTabsEnum::CHANGELOG->value => $this->tab == WebsiteTabsEnum::CHANGELOG->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($website))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($website)))
            ]
        )->table(
            IndexWebUsers::make()->tableStructure(
                parent: $website,
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
        )->table(IndexHistory::make()->tableStructure(prefix: WebsiteTabsEnum::CHANGELOG->value));
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
                            'label' => $website->code,
                        ],
                    ],
                    'simple'         => [
                        'route' => $routeParameters['model'],
                        'label' => $website->code
                    ],
                    'suffix'         => $suffix

                ],
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.web.websites.show',
            'grp.org.shops.show.web.websites.edit' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    'modelWithIndex',
                    Website::where('slug', $routeParameters['website'])->first(),
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.web.websites.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'shop'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.web.websites.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                ),
            ),
            'grp.org.fulfilments.show.web.websites.show',
            'grp.org.fulfilments.show.web.websites.edit' =>
            array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    'modelWithIndex',
                    Website::where('slug', $routeParameters['website'])->first(),
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.web.websites.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.web.websites.show',
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
            'grp.org.websites.show' => [
                'label' => $website->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $website->shop->organisation->slug,
                        'website'      => $website->slug
                    ]
                ]
            ],
        };
    }
}
