<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:03:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Market\Offer\StoreOffer;
use App\Actions\Market\Offer\UpdateOffer;
use App\Actions\Market\OfferCampaign\StoreOfferCampaign;
use App\Actions\Market\OfferCampaign\UpdateOfferCampaign;
use App\Actions\Market\OfferComponent\StoreOfferComponent;
use App\Actions\Market\OfferComponent\UpdateOfferComponent;
use App\Actions\Market\Product\DeleteProduct;
use App\Actions\Market\Product\StoreProduct;
use App\Actions\Market\Product\UpdateProduct;
use App\Actions\Market\ProductCategory\StoreProductCategory;
use App\Actions\Market\ProductCategory\UpdateProductCategory;
use App\Actions\Market\Shop\StoreShop;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Enums\Marketing\Product\ProductTypeEnum;
use App\Models\Marketing\Offer;
use App\Models\Marketing\OfferCampaign;
use App\Models\Marketing\OfferComponent;
use App\Models\Marketing\Product;
use App\Models\Marketing\ProductCategory;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $tenant = Tenant::first();
    if (!$tenant) {
        $group  = StoreGroup::make()->asAction(Group::factory()->definition());
        $tenant = StoreTenant::make()->action($group, Tenant::factory()->definition());
    }
    $tenant->makeCurrent();
});

test('create shop', function () {
    $shop = StoreShop::make()->action(Shop::factory()->definition());

    expect($shop)->toBeInstanceOf(Shop::class);

    return $shop;
});


test('create offer campaign', function ($shop) {
    $offerCampaign = StoreOfferCampaign::make()->action($shop, OfferCampaign::factory()->definition());
    $this->assertModelExists($offerCampaign);

    return $offerCampaign;
})->depends('create shop');

test('update offer campaign', function ($offerCampaign) {
    $offerCampaign = UpdateOfferCampaign::make()->action($offerCampaign, OfferCampaign::factory()->definition());
    $this->assertModelExists($offerCampaign);
})->depends('create offer campaign');

test('create offer', function ($offerCampaign) {
    $offer = StoreOffer::make()->action($offerCampaign, Offer::factory()->definition());
    $this->assertModelExists($offer);

    return $offer;
})->depends('create offer campaign');

test('update offer', function ($offer) {
    $offer = UpdateOffer::make()->action($offer, Offer::factory()->definition());
    $this->assertModelExists($offer);
})->depends('create offer');

test('create offer component', function ($offerCampaign) {
    $offerComponent = StoreOfferComponent::make()->action($offerCampaign, OfferComponent::factory()->definition());
    $this->assertModelExists($offerComponent);

    return $offerComponent;
})->depends('create offer campaign');

test('update offer component', function ($offerComponent) {
    $offerComponent = UpdateOfferComponent::make()->action($offerComponent, OfferComponent::factory()->definition());
    $this->assertModelExists($offerComponent);
})->depends('create offer component');


test('create product category', function ($shop) {
    $productCategory = StoreProductCategory::make()->action($shop, ProductCategory::factory()->definition());
    $this->assertModelExists($productCategory);

    return $productCategory;
})->depends('create shop');

test('create sub product category', function ($productCategory) {
    $subProductCategory = StoreProductCategory::make()->action($productCategory, ProductCategory::factory()->definition());
    $this->assertModelExists($subProductCategory);

    return $subProductCategory;
})->depends('create product category');

test('create product category 2', function ($shop) {
    $productCategory = StoreProductCategory::make()->action($shop, ProductCategory::factory()->definition());
    $this->assertModelExists($productCategory);

    return $productCategory;
})->depends('create shop');

test('update product category', function ($productCategory) {
    $productCategory = UpdateProductCategory::make()->action($productCategory, ProductCategory::factory()->definition());

    $this->assertModelExists($productCategory);
})->depends('create product category');

test('create product', function ($shop) {
    $productData = array_merge(
        Product::factory()->definition(),
        [
            'owner_type'=> 'Tenant',
            'owner_id'  => app('currentTenant')->id,
            'type'      => ProductTypeEnum::PHYSICAL_GOOD->value,

        ]
    );
    $product     = StoreProduct::make()->action($shop, $productData);
    $this->assertModelExists($product);

    return $product;
})->depends('create shop');

test('update product', function ($product) {
    $product = UpdateProduct::make()->action($product, Product::factory()->definition());

    $this->assertModelExists($product);
})->depends('create product');

test('delete product', function ($product) {
    $product = DeleteProduct::run($product);

    $this->assertModelExists($product);
})->depends('create product');
