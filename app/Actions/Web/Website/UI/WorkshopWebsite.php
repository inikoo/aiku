<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 29 May 2023 12:18:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\WithInertia;
use App\Enums\UI\WebsiteTabsEnum;
use App\Enums\UI\WorkshopTabsEnum;
use App\Http\Resources\Market\WebsiteResource;
use App\Models\Market\Shop;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @property Website $website
 */
class WorkshopWebsite extends InertiaAction
{
    use AsAction;
    use WithInertia;


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->can('websites.edit');
        $this->canDelete = $request->user()->can('websites.edit');
        return $request->user()->hasPermissionTo("websites.view");
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
            'Web/Workshop',
            [
                'title'       => __('Workshop'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead'    => [
                    'title'   => $website->name,
                ],
                'tabs'                                => [
                    'current'    => $this->tab,
                    'navigation' => WorkshopTabsEnum::navigation()
                ],
                WorkshopTabsEnum::HEADER->value => $this->tab == WorkshopTabsEnum::HEADER->value ?
                    fn () => WebsiteResource::collection(IndexWebsitesHeader::run($website))
                    : Inertia::lazy(fn () => WebsiteResource::collection(IndexWebsitesHeader::run($website))),
                WorkshopTabsEnum::MENU->value => $this->tab == WorkshopTabsEnum::MENU->value ?
                    fn () => WebsiteResource::collection(IndexWebsitesMenu::run($website))
                    : Inertia::lazy(fn () => WebsiteResource::collection(IndexWebsitesMenu::run($website))),
                WorkshopTabsEnum::FOOTER->value => $this->tab == WorkshopTabsEnum::FOOTER->value ?
                    fn () => WebsiteResource::collection(IndexWebsitesFooter::run($website))
                    : Inertia::lazy(fn () => WebsiteResource::collection(IndexWebsitesFooter::run($website)))
            ]
        );
    }


    public function jsonResponse(Website $website): WebsiteResource
    {
        return new WebsiteResource($website);
    }



    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (Website $website, array $routeParameters, string $suffix) {
            return [
                [

                    'type'           => 'website',
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
            'shops.show.websites.show',
            'shops.show.websites.edit'
            => array_merge(
                ShowWebsite::make()->getBreadcrumbs(
                    $routeName,
                    $routeParameters
                ),
                $headCrumb(
                    $this->website,
                    $routeParameters['website'],
                    $suffix
                )
            ),
            default => []
        };
    }
}
