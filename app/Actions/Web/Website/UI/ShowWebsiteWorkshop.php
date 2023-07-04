<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:31:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\InertiaAction;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\UI\Dashboard\Dashboard;
use App\Actions\Web\Website\GetWebsiteWorkshopFooter;
use App\Actions\Web\Website\GetWebsiteWorkshopHeader;
use App\Actions\Web\Website\GetWebsiteWorkshopMenu;
use App\Enums\UI\WebsiteWorkshopTabsEnum;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowWebsiteWorkshop extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->can('websites.edit');
        $this->canDelete = $request->user()->can('websites.edit');
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
                'title'                            => __('workshop'),
                'breadcrumbs'                      => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),

                'pageHead'                         => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'globe'],
                            'title' => __('website')
                        ],
                    'title'   => $website->name,


                ],
                'tabs'                                   => [

                    'current'    => $this->tab,
                    'navigation' => WebsiteWorkshopTabsEnum::navigation(),


                ],


                WebsiteWorkshopTabsEnum::HEADER->value       => $this->tab == WebsiteWorkshopTabsEnum::HEADER->value ?
                    fn () => GetWebsiteWorkshopHeader::run($website)
                    : Inertia::lazy(
                        fn () => GetWebsiteWorkshopHeader::run($website)
                    ),
                WebsiteWorkshopTabsEnum::MENU->value => $this->tab == WebsiteWorkshopTabsEnum::MENU->value
                    ?
                    fn () => GetWebsiteWorkshopMenu::run($website)
                    : Inertia::lazy(fn () => GetWebsiteWorkshopMenu::run($website)),

                WebsiteWorkshopTabsEnum::FOOTER->value => $this->tab == WebsiteWorkshopTabsEnum::FOOTER->value ?
                    fn () => GetWebsiteWorkshopFooter::run($website)
                    : Inertia::lazy(fn () => GetWebsiteWorkshopFooter::run($website)),

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
                    'simple'=> [
                        'route' => $routeParameters['model'],
                        'label' => $website->name
                    ],


                    'suffix'=> $suffix

                ],
            ];
        };



        return match ($routeName) {
            'websites.workshop' =>

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


}
