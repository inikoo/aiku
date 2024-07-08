<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Portfolio\Banner\UI;

use App\Actions\Helpers\History\IndexCustomerHistory;
use App\Actions\InertiaAction;
use App\Actions\Portfolio\PortfolioWebsite\UI\ShowPortfolioWebsite;
use App\Enums\UI\Customer\BannerTabsEnum;
use App\Http\Resources\History\CustomerHistoryResource;
use App\Http\Resources\Portfolio\BannerResource;
use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Models\Portfolio\Banner;
use App\Models\Portfolio\PortfolioWebsite;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowDeletedBanner extends InertiaAction
{
    private Customer|Shop|PortfolioWebsite|Organisation $parent;

    public function handle(Organisation|Shop|Customer|PortfolioWebsite $parent, Banner $banner): Banner
    {
        $this->parent = $parent;

        return $banner;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canRestore   = $request->get('customerUser')->hasPermissionTo('portfolio.banners.edit');

        return
            (
                $request->get('customerUser')->hasPermissionTo('portfolio.banners.view')
            );
    }

    public function asController(Banner $banner, ActionRequest $request): Banner
    {
        $this->initialisation($request)->withTab(BannerTabsEnum::values());
        return $this->handle($request->get('customer'), $banner);
    }

    public function inCustomer(Banner $banner, ActionRequest $request): Banner
    {
        $this->initialisation($request)->withTab(BannerTabsEnum::values());

        return $banner;
    }

    /*
    public function inPortfolioWebsite(PortfolioWebsite $portfolioWebsite, Banner $banner, ActionRequest $request): Banner
    {
        $this->initialisation($request)->withTab(BannerTabsEnum::values());

        return $banner;
    }
    */


    public function htmlResponse(Banner $banner, ActionRequest $request): Response
    {
        $customer = $request->get('customer');

        return Inertia::render(
            'Banners/Banner',
            [
                'breadcrumbs'                    => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'                          => $banner->name,
                'pageHead'                       => [
                    'title'   => $banner->name,
                    'icon'    => [
                        'title' => __('banner'),
                        'icon'  => 'fal fa-sign'
                    ],
                    'actions' => [
                        $this->canRestore ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'label' => __('restore'),
                            'icon'  => ["fal", "fa-trash-restore-alt"],
                            'route' => [
                                'name'       => preg_replace('/show$/', 'workshop', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false,
                    ],
                ],
                'tabs'                           => [
                    'current'    => $this->tab,
                    'navigation' => BannerTabsEnum::navigation()
                ],
                BannerTabsEnum::SHOWCASE->value => $this->tab == BannerTabsEnum::SHOWCASE->value
                    ?
                    fn () => BannerResource::make($banner)->getArray()
                    : Inertia::lazy(
                        fn () => BannerResource::make($banner)->getArray()
                    ),
                BannerTabsEnum::CHANGELOG->value => $this->tab == BannerTabsEnum::CHANGELOG->value
                    ?
                    fn () => CustomerHistoryResource::collection(
                        IndexCustomerHistory::run(
                            customer: $customer,
                            model: $banner,
                            prefix:  BannerTabsEnum::CHANGELOG->value
                        )
                    )
                    : Inertia::lazy(fn () => CustomerHistoryResource::collection(
                        IndexCustomerHistory::run(
                            customer: $customer,
                            model: $banner,
                            prefix:  BannerTabsEnum::CHANGELOG->value
                        )
                    )),

            ]
        )->table(
            IndexCustomerHistory::make()->tableStructure(
                prefix: BannerTabsEnum::CHANGELOG->value
            )
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (string $type, Banner $banner, array $routeParameters, string $suffix = null) {
            return [
                [
                    'type'           => $type,
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('banners')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $banner->name,
                        ],

                    ],
                    'simple'         => [
                        'route' => $routeParameters['model'],
                        'label' => $banner->name
                    ],
                    'suffix' => $suffix
                ],
            ];

        };
        return match ($routeName) {
            'customer.portfolio.websites.show.banners.deleted' =>
            array_merge(
                ShowPortfolioWebsite::make()->getBreadcrumbs(
                    'customer.portfolio.websites.show',
                    ['website' => $routeParameters['portfolioWebsite']]
                ),
                $headCrumb(
                    'modelWithIndex',
                    $routeParameters['banner'],
                    [
                        'index' => [
                            'name'       => 'customer.portfolio.websites.show.banners.index',
                            'parameters' => [$routeParameters['portfolioWebsite']->slug]
                        ],
                        'model' => [
                            'name'       => 'customer.portfolio.websites.show.banners.deleted',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                ),
            ),

            default => []
        };
    }
}
