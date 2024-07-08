<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Portfolio\Banner\UI;

use App\Actions\InertiaAction;
use App\Models\Portfolio\Banner;
use App\Models\Portfolio\PortfolioWebsite;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveBanner extends InertiaAction
{
    public function handle(Banner $banner): Banner
    {
        return $banner;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->get('customerUser')->hasPermissionTo("portfolio.banners.edit");
    }

    public function asController(Banner $banner, ActionRequest $request): Banner
    {
        $this->initialisation($request);

        return $this->handle($banner);
    }

    public function inPortfolioWebsite(PortfolioWebsite $portfolioWebsite, Banner $banner, ActionRequest $request): Banner
    {
        $this->initialisation($request);

        return $this->handle($banner);
    }


    public function getAction($route): array
    {
        return [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete banner'),
            'text'        => __("This action will delete this banner"),
            'route'       => $route
        ];
    }

    public function htmlResponse(Banner $banner, ActionRequest $request): Response
    {
        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete banner'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'    =>
                        [
                            'icon'  => 'fal fa-sign',
                            'title' => __('banner')
                        ],
                    'title'   => $banner->slug,
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ]
                ],
                'data'        => $this->getAction(
                    route: [
                        'name'       => 'customer.models.banner.delete',
                        'parameters' => [
                            'banner' => $banner->id
                        ]
                    ]
                )


            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowBanner::make()->getBreadcrumbs(
            routeName: preg_replace('/remove$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('deleting').')'
        );
    }
}
