<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:31:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\Web\Website\GetWebsiteWorkshopCategory;
use App\Actions\Web\Website\GetWebsiteWorkshopColorScheme;
use App\Actions\Web\Website\GetWebsiteWorkshopFooter;
use App\Actions\Web\Website\GetWebsiteWorkshopHeader;
use App\Actions\Web\Website\GetWebsiteWorkshopMenu;
use App\Actions\Web\Website\GetWebsiteWorkshopProduct;
use App\Enums\UI\WebsiteWorkshopTabsEnum;
use App\Models\Web\Website;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowWebsiteWorkshop extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo('websites.edit');
        $this->canDelete = $request->user()->hasPermissionTo('websites.edit');

        return $request->user()->hasPermissionTo("websites.view");
    }

    public function asController(Website $website, ActionRequest $request): Website
    {
        $this->initialisation($request)->withTab(WebsiteWorkshopTabsEnum::values());

        return $website;
    }


    public function htmlResponse(Website $website, ActionRequest $request): Response
    {

        return Inertia::render(
            'Web/WebsiteWorkshop',
            [
                'title'       => __("Website's workshop"),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($website, $request),
                    'next'     => $this->getNext($website, $request),
                ],
                'pageHead'    => [

                    'title'    => __('Workshop'),
                    'container'=> [
                        'icon'    => ['fal', 'fa-globe'],
                        'tooltip' => __('Website'),
                        'label'   => Str::possessive($website->name)
                    ],
                    'iconRight'    =>
                        [
                            'icon'  => ['fal', 'drafting-compass'],
                            'title' => __("Website's workshop")
                        ],

                    'actions' => [
                        [
                            'type'       => 'button',
                            'style'      => 'exitEdit',
                            'label'      => __('Exit workshop'),
                            'route'      => [
                                'name'       => preg_replace('/workshop$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters()),
                            ]
                        ]
                    ],
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => WebsiteWorkshopTabsEnum::navigation(),
                ],

                WebsiteWorkshopTabsEnum::COLOR_SCHEME->value => $this->tab == WebsiteWorkshopTabsEnum::COLOR_SCHEME->value
                    ?
                    fn () => GetWebsiteWorkshopColorScheme::run($website)
                    : Inertia::lazy(
                        fn () => GetWebsiteWorkshopColorScheme::run($website)
                    ),

                WebsiteWorkshopTabsEnum::HEADER->value => $this->tab == WebsiteWorkshopTabsEnum::HEADER->value
                    ?
                    fn () => GetWebsiteWorkshopHeader::run($website)
                    : Inertia::lazy(
                        fn () => GetWebsiteWorkshopHeader::run($website)
                    ),
                WebsiteWorkshopTabsEnum::MENU->value   => $this->tab == WebsiteWorkshopTabsEnum::MENU->value
                    ?
                    fn () => GetWebsiteWorkshopMenu::run($website)
                    : Inertia::lazy(fn () => GetWebsiteWorkshopMenu::run($website)),

                WebsiteWorkshopTabsEnum::FOOTER->value   => $this->tab == WebsiteWorkshopTabsEnum::FOOTER->value ?
                    fn () => GetWebsiteWorkshopFooter::run($website)
                    : Inertia::lazy(fn () => GetWebsiteWorkshopFooter::run($website)),
                WebsiteWorkshopTabsEnum::CATEGORY->value => $this->tab == WebsiteWorkshopTabsEnum::CATEGORY->value
                    ?
                    fn () => GetWebsiteWorkshopCategory::run($website)
                    : Inertia::lazy(
                        fn () => GetWebsiteWorkshopCategory::run($website)
                    ),
                WebsiteWorkshopTabsEnum::PRODUCT->value  => $this->tab == WebsiteWorkshopTabsEnum::PRODUCT->value
                    ?
                    fn () => GetWebsiteWorkshopProduct::run($website)
                    : Inertia::lazy(
                        fn () => GetWebsiteWorkshopProduct::run($website)
                    ),

            ]
        );
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
            'grp.org.shops.show.websites.workshop' =>

            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    'modelWithIndex',
                    $routeParameters['website'],
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.websites.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.websites.show',
                            'parameters' => [$routeParameters['website']->slug]
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
            'grp.org.shops.show.websites.workshop' => [
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
