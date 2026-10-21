<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\UI\Dropshipping\Marketing;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\CRM\TrafficSource\Attribution\GetShopAcquisitionCohorts;
use App\Actions\CRM\TrafficSource\Attribution\GetShopOrderAttribution;
use App\Actions\CRM\TrafficSource\Attribution\GetShopOrderJourney;
use App\Actions\OrgAction;
use App\Actions\Traits\Dashboards\WithMarketingPeriod;
use App\Enums\UI\Marketing\MarketingDashboardV2TabsEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowMarketingDashboardV2 extends OrgAction
{
    use WithMarketingPeriod;

    private ?string $journeyOrder = null;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo("marketing.{$this->shop->id}.view");
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): ActionRequest
    {
        $this->initialisationFromShop($shop, $request)->withTab(MarketingDashboardV2TabsEnum::values());

        $this->setMarketingPeriod($request->user()->settings);

        $this->journeyOrder = $request->query('order');

        return $request;
    }

    public function htmlResponse(ActionRequest $request): Response
    {
        $title = __('Marketing Dashboard');

        return Inertia::render(
            'Org/Marketing/MarketingDashboardV2',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => $title,
                'pageHead'    => [
                    'icon'  => [
                        'icon'  => ['fal', 'fa-bullhorn'],
                        'title' => $title,
                    ],
                    'title' => $title,
                    'afterTitle' => [
                        'label' => __('Attribution'),
                    ],
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => MarketingDashboardV2TabsEnum::navigation(),
                ],
                'intervals'   => $this->intervalsProp($request->user()->settings),
                'settings'    => [],

                MarketingDashboardV2TabsEnum::ACQUISITION->value => $this->tab == MarketingDashboardV2TabsEnum::ACQUISITION->value
                    ? fn () => $this->acquisition()
                    : Inertia::optional(fn () => $this->acquisition()),
                MarketingDashboardV2TabsEnum::ORDERS->value => $this->tab == MarketingDashboardV2TabsEnum::ORDERS->value
                    ? fn () => $this->orders()
                    : Inertia::optional(fn () => $this->orders()),
                MarketingDashboardV2TabsEnum::JOURNEY->value => $this->tab == MarketingDashboardV2TabsEnum::JOURNEY->value
                    ? fn () => GetShopOrderJourney::run($this->shop, $this->journeyOrder)
                    : Inertia::optional(fn () => GetShopOrderJourney::run($this->shop, $this->journeyOrder)),
            ]
        );
    }

    private function acquisition(): array
    {
        return array_merge(
            GetShopAcquisitionCohorts::run($this->shop, $this->periodFrom, $this->periodTo),
            $this->periodLabels()
        );
    }

    private function orders(): array
    {
        return array_merge(
            GetShopOrderAttribution::run($this->shop, $this->periodFrom, $this->periodTo),
            $this->periodLabels()
        );
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            ShowShop::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.shops.show.marketing.dashboard',
                            'parameters' => $routeParameters,
                        ],
                        'label' => __('Marketing'),
                    ],
                ],
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.shops.show.marketing.dashboard_v2',
                            'parameters' => $routeParameters,
                        ],
                        'label' => __('Attribution'),
                    ],
                ],
            ]
        );
    }
}
