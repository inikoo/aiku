<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 27 Dec 2024 21:46:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Masters\MasterAsset;

use App\Actions\Catalogue\Product\StoreProductFromMasterProduct;
use App\Actions\Masters\MasterProductCategory\StoreFamilyFromMasterFamily;
use App\Actions\Catalogue\Variant\TranslateVariantOptions;
use App\Actions\OrgAction;
use App\Actions\Web\Redirect\StoreRedirectFromWebsite;
use App\Actions\Web\Redirect\UpdateRedirect;
use App\Actions\Web\Webpage\UpdateWebpage;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Http\Resources\Catalogue\ProductResourceForVariant;
use App\Models\Catalogue\Variant;
use App\Models\Web\Redirect;
use App\Actions\Traits\Authorisations\WithMastersEditAuthorisation;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Shop;
use App\Models\Masters\MasterAsset;
use App\Models\Masters\MasterProductCategory;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class CloneMasterAssetToOtherShop extends OrgAction
{
    use WithNoStrictRules;
    use WithMastersEditAuthorisation;

    /**
     * @var \App\Models\Masters\MasterProductCategory
     */
    private MasterProductCategory $masterFamily;
    private MasterAsset $masterAsset;
    /**
     * @throws \Throwable
     */
    public function handle(MasterProductCategory $masterFamily, array $modelData): MasterProductCategory
    {
        $shopProducts = Arr::pull($modelData, 'shop_products', []);

        foreach (array_keys($shopProducts) as $shop_id) {
            $haveChildFamily = $masterFamily->productCategories()->where('product_categories.shop_id', $shop_id)->exists();

            if (!$haveChildFamily) {
                StoreFamilyFromMasterFamily::make()->action($masterFamily, [
                    'shop_family' => [
                        $shop_id => [
                            'create_webpage'    => true,
                        ]
                    ],
                ]);
            }
        }

        StoreProductFromMasterProduct::make()->action($this->masterAsset, [
            'shop_products' => $shopProducts,
            'is_minion_variant' => $this->masterAsset->is_minion_variant,
        ]);

        foreach ($shopProducts as $shopId => $shopProduct) {
            if (Arr::get($shopProduct, 'create_in_shop') == 'Yes') {
                $this->addToShopVariant((int) $shopId);
            }
        }

        return $masterFamily;
    }

    /**
     * A variant member added to a shop after its leader: the shop variant was built when the
     * leader arrived and only lists the members the shop had then. Lists this one too and sends
     * its address to the leader's page, touching no other member.
     *
     * @throws \Throwable
     */
    private function addToShopVariant(int $shopId): void
    {
        $masterVariant = $this->masterAsset->masterVariant;
        $option        = data_get($masterVariant?->data, 'products.'.$this->masterAsset->id);
        $product       = $this->masterAsset->products()->where('shop_id', $shopId)->orderBy('id')->first();
        $variant       = $product?->variant_id ? Variant::find($product->variant_id) : null;

        if (!$option || !$variant || !$variant->status || $variant->master_variant_id != $masterVariant->id || Arr::has($variant->data, 'products.'.$product->id)) {
            return;
        }

        $data = $variant->data;
        data_set($data, 'products.'.$product->id, array_merge(Arr::except($option, 'product'), [
            'product' => ProductResourceForVariant::make($product)->resolve(),
        ]));
        $productIds = array_keys($data['products']);

        $variant->update([
            'data'                       => $data,
            'number_used_slots'          => count($productIds),
            'number_used_slots_for_sale' => Product::whereIn('id', $productIds)->where('is_for_sale', true)->count(),
        ]);
        TranslateVariantOptions::dispatch($variant);

        $leaderWebpage = $variant->leaderProduct?->webpage;
        $website       = $product->shop->website;
        if (!$leaderWebpage || !$website) {
            return;
        }

        if ($webpage = $product->webpage) {
            UpdateWebpage::make()->action($webpage, [
                'state_data' => [
                    'state'               => WebpageStateEnum::CLOSED->value,
                    'redirect_webpage_id' => $leaderWebpage->id,
                ]
            ]);

            return;
        }

        $redirect = Redirect::where('from_path', strtolower($product->code))->where('shop_id', $shopId)->first();
        if ($redirect) {
            UpdateRedirect::make()->action($redirect, ['to_webpage_id' => $leaderWebpage->id]);

            return;
        }

        StoreRedirectFromWebsite::make()->action($website, [
            'from_url' => strtolower($product->code),
            'to_url'   => $leaderWebpage->id,
        ]);
    }

    public function asController(String $masterFamily, ActionRequest $request): MasterProductCategory
    {
        $this->masterFamily = MasterProductCategory::find($masterFamily);
        $this->initialisationFromGroup($this->masterFamily->group, $request);

        return $this->handle($this->masterFamily, $this->validatedData);
    }

    public function prepareForValidation(): void
    {
        if ($this->has('masterProduct')) {
            $this->masterAsset = MasterAsset::where('slug', $this->masterProduct)->first();
            $this->set('masterAsset', $this->masterAsset->replicate()->toArray());
        }
    }

    public function afterValidator(Validator $validator): void
    {
        $masterVariant = isset($this->masterAsset) ? $this->masterAsset->masterVariant : null;
        if (!$masterVariant || $masterVariant->leader_id == $this->masterAsset->id) {
            return;
        }

        $shopIds = collect($this->get('shop_products', []))
            ->filter(fn ($shopProduct) => Arr::get($shopProduct, 'create_in_shop') == 'Yes')
            ->keys();
        $shopsWithLeader     = Product::where('master_product_id', $masterVariant->leader_id)->whereIn('shop_id', $shopIds)->pluck('shop_id');
        $shopsWithoutLeader  = Shop::whereIn('id', $shopIds->diff($shopsWithLeader))->orderBy('code')->pluck('code');

        if ($shopsWithoutLeader->isNotEmpty()) {
            $validator->errors()->add('shop_products', __('This product is part of the variant :variant. Add its leader :leader to :shops first, then this product.', [
                'variant' => $masterVariant->code,
                'leader'  => $masterVariant->leaderMasterProduct?->code,
                'shops'   => $shopsWithoutLeader->implode(', '),
            ]));
        }
    }

    public function rules(): array
    {
        $rules = [
            'masterFamily'                      => ['required', 'int'],
            'masterAsset'                       => ['required', 'array'],
            'masterAsset.unit'                  => ['required', 'string'],
            'masterAsset.units'                 => ['required', 'numeric'],
            'shop_products'                     => ['sometimes', 'array'],
            'shop_products.*.create_in_shop'    => ['sometimes', 'string'],
            'shop_products.*.price'             => ['required', 'min:0'],
            'shop_products.*.rrp'               => ['required', 'min:0'],
        ];

        return $rules;
    }

    public function getValidationMessages(): array
    {
        return [
            'masterAsset.unit.required'  => __('Master product have missing Unit. Please edit master product first'),
            'masterAsset.units.required' => __('Master product have missing Units. Please contact administrator to fix this issue'),
            'shop_products.*.price'      => __('Required to input Price on selected shop'),
            'shop_products.*.rrp'        => __('Required to input RRP on selected shop'),
        ];
    }
}
