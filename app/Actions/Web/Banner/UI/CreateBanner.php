<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Banner\UI;

use App\Actions\OrgAction;
use App\Enums\Web\Banner\BannerTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateBanner extends OrgAction
{
    private Shop $parent;
    private Website $website;

    public function asController(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): Response
    {
        $this->parent  = $shop;
        $this->website = $website;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($request);
    }

    public function handle(ActionRequest $request): Response
    {
        $fields = [];

        $fields[] = [
            'title'  => '',
            'fields' => [
                'type' => [
                    'type'        => 'radio',
                    'label'       => __('orientation'),
                    'required'    => true,
                    'value'       => BannerTypeEnum::LANDSCAPE,
                    'options'     => Options::forEnum(BannerTypeEnum::class)

                ],

                'name' => [
                    'type'        => 'input',
                    'label'       => __('name'),
                    'placeholder' => __('Name for new banner'),
                    'required'    => true,
                    'value'       => '',
                ],
            ]
        ];

        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('new banner'),
                'pageHead'    => [
                    'title'   => __('banner'),
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'route' => [
                                'name'       => preg_replace('/create$/', 'index', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ]
                ],
                'formData'    => [
                    'blueprint' => $fields,
                    'route'     =>
                        match (class_basename($this->parent)) {
                            'Shop' => [
                                'name'       => 'grp.models.shop.website.banner.store',
                                'parameters' => [
                                    'shop'    => $this->parent->id,
                                    'website' => $this->website->id
                                ]
                            ],
                            default => [
                                'name'       => 'grp.models.banner.store'
                            ],
                        }
                ],
            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexBanners::make()->getBreadcrumbs(
                'grp.org.shops.show.web.banners.index',
                $routeParameters
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __("creating banner"),
                    ]
                ]
            ]
        );
    }
}
