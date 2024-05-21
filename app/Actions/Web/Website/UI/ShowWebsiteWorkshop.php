<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:31:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Actions\Web\Website\GetWebsiteWorkshopCategory;
use App\Actions\Web\Website\GetWebsiteWorkshopColorScheme;
use App\Actions\Web\Website\GetWebsiteWorkshopMenu;
use App\Actions\Web\Website\GetWebsiteWorkshopProduct;
use App\Enums\UI\Web\WebsiteWorkshopTabsEnum;
use App\Http\Resources\Web\WebsiteLayoutWorkshopResource;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowWebsiteWorkshop extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("web.{$this->shop->id}.edit");
        return $request->user()->hasPermissionTo("web.{$this->shop->id}.view");

    }

    public function asController(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): Website
    {
        $this->initialisationFromShop($shop, $request)->withTab(WebsiteWorkshopTabsEnum::values());
        return $website;
    }


    public function htmlResponse(Website $website, ActionRequest $request): Response
    {

        return Inertia::render(
            'Org/Web/WebsiteWorkshop',
            [
                'title'       => __("Website's workshop"),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($website, $request),
                    'next'     => $this->getNext($website, $request),
                ],
                'pageHead'    => [

                    'title'        => __('Workshop'),
                    'iconRight'    =>
                        [
                            'icon'  => ['fal', 'drafting-compass'],
                            'title' => __("Website's workshop")
                        ],

                    'actions' => [
                        [
                            'type'       => 'button',
                            'style'      => 'exit',
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

                WebsiteWorkshopTabsEnum::MENU->value   => $this->tab == WebsiteWorkshopTabsEnum::MENU->value
                    ?
                    fn () => GetWebsiteWorkshopMenu::run($website)
                    : Inertia::lazy(fn () => GetWebsiteWorkshopMenu::run($website)),

                WebsiteWorkshopTabsEnum::PAGE_LAYOUT->value   => $this->tab == WebsiteWorkshopTabsEnum::PAGE_LAYOUT->value ?
                    fn () => WebsiteLayoutWorkshopResource::make($website)
                    : Inertia::lazy(fn () => WebsiteLayoutWorkshopResource::make($website)),

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

        $website=Website::where('slug', $routeParameters['website'])->first();

        return match ($routeName) {
            'grp.org.shops.show.web.websites.workshop' =>

            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    'modelWithIndex',
                    $website,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.web.websites.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.web.websites.show',
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
            'grp.org.shops.show.web.websites.workshop' => [
                'label' => $website->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'=> $website->organisation->slug,
                        'shop'        => $website->shop->slug,
                        'website'     => $website->slug
                    ]
                ]
            ]
        };
    }

}
