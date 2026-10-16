<?php

/*
 * Author: Rifqi <rifqitaufiqurrohman1@gmail.com>
 * Created: Thu, 15 Oct 2026
 * Copyright (c) 2026
 */

namespace App\Actions\Masters\MasterAsset\UI;

use App\Actions\Masters\MasterAsset\WithMasterProductSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithMastersAuthorisation;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Helpers\Country;
use App\Models\Masters\MasterAsset;
use App\Models\Masters\MasterShop;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * The webmaster's view of a master product in each of its shops: what the shop follows from
 * the master, how its webpage appears in search, and the checklist and workflow per shop.
 */
class ShowMasterProductSetting extends OrgAction
{
    use WithMastersAuthorisation;
    use WithMasterProductSubNavigation;

    public function handle(MasterAsset $masterAsset): MasterAsset
    {
        return $masterAsset;
    }

    public function asController(MasterShop $masterShop, MasterAsset $masterProduct, ActionRequest $request): MasterAsset
    {
        if ($masterShop->id != $masterProduct->master_shop_id) {
            abort(404);
        }

        $this->initialisationFromGroup($masterShop->group, $request);

        return $this->handle($masterProduct);
    }

    public function htmlResponse(MasterAsset $masterAsset, ActionRequest $request): Response
    {
        $products = $masterAsset->products()
            ->with(['shop.currency', 'organisation', 'webpage.website', 'currency'])
            ->get()
            ->filter(fn (Product $product) => $product->shop && $product->shop->state != ShopStateEnum::CLOSED)
            ->sortBy(fn (Product $product) => $product->shop->code)
            ->values();

        $checklistRow = GetMasterProductChecklist::make()->checklistRow($masterAsset);

        return Inertia::render(
            'Masters/MasterProductSetting',
            [
                'title'       => __('Product setting').': '.$masterAsset->code,
                'breadcrumbs' => ShowMasterProduct::make()->getBreadcrumbs(
                    masterAsset: $masterAsset,
                    routeName: 'grp.masters.master_shops.show.master_products.show',
                    routeParameters: $request->route()->originalParameters(),
                    suffix: '('.__('Product setting').')'
                ),
                'pageHead'    => [
                    'model'         => __('Product setting'),
                    'title'         => $masterAsset->code,
                    'afterTitle'    => [
                        'label' => $masterAsset->name,
                    ],
                    'icon'          => [
                        'icon'  => ['fal', 'fa-sliders-h'],
                        'title' => __('Product setting'),
                    ],
                    'actions'       => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => 'grp.masters.master_shops.show.master_products.show',
                                'parameters' => [
                                    'masterShop'    => $masterAsset->masterShop->slug,
                                    'masterProduct' => $masterAsset->slug,
                                ]
                            ]
                        ]
                    ],
                    'subNavigation' => $this->getMasterProductsSubNavigation($masterAsset),
                ],
                'canEdit'     => $this->canEdit,
                'master'      => $this->getMasterData($masterAsset),
                'shops'       => $products->map(fn (Product $product) => $this->getShopData($masterAsset, $product, $checklistRow))->all(),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getMasterData(MasterAsset $masterAsset): array
    {
        $dimensions = collect(Arr::only($masterAsset->marketing_dimensions ?? [], ['l', 'w', 'h']))
            ->filter(fn ($value) => (float)$value > 0)
            ->implode(' × ');

        return [
            'code'              => $masterAsset->code,
            'name'              => $masterAsset->name,
            'description'       => $masterAsset->description,
            'description_extra' => $masterAsset->description_extra,
            'price'             => $masterAsset->price,
            'currency_code'     => $masterAsset->group->currency->code,
            'marketing_weight'  => $masterAsset->marketing_weight,
            'gross_weight'      => $masterAsset->gross_weight,
            'dimensions'        => $dimensions !== '' ? $dimensions.' '.Arr::get($masterAsset->marketing_dimensions, 'units', '') : null,
            'ingredients'       => $masterAsset->marketing_ingredients,
            'barcode'           => $masterAsset->barcode ?: $masterAsset->tradeUnits->pluck('barcode')->filter()->first(),
            'origin_country'    => $masterAsset->origin_country_id ? Country::find($masterAsset->origin_country_id)?->name : $masterAsset->country_of_origin,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getShopData(MasterAsset $masterAsset, Product $product, MasterAsset $checklistRow): array
    {
        $webpage = $product->webpage;

        return [
            'id'        => $product->id,
            'shop'      => [
                'code' => $product->shop->code,
                'name' => $product->shop->name,
            ],
            'product'   => [
                'code'                => $product->code,
                'name'                => $product->name,
                'description'         => $product->description,
                'description_extra'   => $product->description_extra,
                'price'               => $product->price,
                'currency_code'       => $product->currency?->code ?? $product->shop->currency->code,
                'follows_master'      => [
                    'name'              => $product->name === $masterAsset->name,
                    'description'       => $product->description === $masterAsset->description,
                    'description_extra' => $product->description_extra === $masterAsset->description_extra,
                    'price'             => !$product->not_follow_master_prices,
                ],
                'route'               => [
                    'name'       => 'grp.org.shops.show.catalogue.products.current_products.show',
                    'parameters' => [
                        'organisation' => $product->organisation->slug,
                        'shop'         => $product->shop->slug,
                        'product'      => $product->slug,
                    ],
                ],
            ],
            'webpage'   => $webpage ? [
                'id'               => $webpage->id,
                'state'            => $webpage->state->value,
                'title'            => $webpage->title,
                'description'      => $webpage->description,
                'url'              => $webpage->url,
                'breadcrumb_label' => $webpage->breadcrumb_label,
                'index_page'       => (bool)$webpage->index_page,
                'follow_link'      => (bool)$webpage->follow_link,
                'structured_data'  => Arr::get($webpage->seo_data, 'structured_data'),
                'update_route'     => [
                    'name'       => 'grp.models.webpage.update',
                    'parameters' => ['webpage' => $webpage->id],
                ],
                'workshop_route'   => $webpage->website ? [
                    'name'       => 'grp.org.shops.show.web.webpages.workshop',
                    'parameters' => [
                        'organisation' => $product->organisation->slug,
                        'shop'         => $product->shop->slug,
                        'website'      => $webpage->website->slug,
                        'webpage'      => $webpage->slug,
                    ],
                ] : null,
            ] : null,
            'checklist' => GetMasterProductChecklist::run($checklistRow, collect([$product])),
            'workflow'  => GetMasterProductWorkflow::run($masterAsset, collect([$product])),
        ];
    }
}
