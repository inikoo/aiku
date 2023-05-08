<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:08:26 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Marketing\Product\DeleteProduct;
use App\Actions\Marketing\Product\StoreProduct;
use App\Actions\Marketing\Product\UpdateProduct;
use App\Actions\Marketing\ProductCategory\StoreProductCategory;
use App\Actions\Marketing\ProductCategory\UpdateProductCategory;
use App\Actions\Marketing\Shop\StoreShop;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Marketing\Product;
use App\Models\Marketing\ProductCategory;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
});

test('create shop', function () {
    $shop = StoreShop::make()->action(Shop::factory()->definition());
    $this->assertModelExists($shop);
    expect($shop->paymentAccounts()->count())->toBe(1)
        ->and($shop->outboxes()->count())->toBe(count(OutboxTypeEnum::values()));


    return $shop;
});

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
    $product = StoreProduct::make()->action($shop, Product::factory()->definition());
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
