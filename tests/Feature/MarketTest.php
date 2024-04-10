<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jan 2024 22:04:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Market\Outer\StoreOuter;
use App\Actions\Market\Product\DeleteProduct;
use App\Actions\Market\Product\StoreNoPhysicalGood;
use App\Actions\Market\Product\StorePhysicalGood;
use App\Actions\Market\Product\UpdateProduct;
use App\Actions\Market\ProductCategory\StoreProductCategory;
use App\Actions\Market\ProductCategory\UpdateProductCategory;
use App\Actions\Market\Shop\StoreShop;
use App\Actions\Market\Shop\UpdateShop;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Enums\Market\Product\ProductUnitRelationshipType;
use App\Enums\Market\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Market\Outer;
use App\Models\Market\Product;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Permission;
use App\Models\SysAdmin\Role;
use App\Models\Web\Website;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->guest        = createAdminGuest($this->organisation->group);
    $this->warehouse    = createWarehouse();

    if(!isset($this->tradeUnit)) {
        $this->tradeUnit = StoreTradeUnit::make()->action(
            $this->organisation->group,
            TradeUnit::factory()->definition()
        );
    }

    if(!isset($this->tradeUnit2)) {
        $this->tradeUnit2 = StoreTradeUnit::make()->action(
            $this->organisation->group,
            TradeUnit::factory()->definition()
        );
    }

});

test('create shop', function () {
    $organisation = $this->organisation;
    $storeData    = Shop::factory()->definition();
    data_set($storeData, 'type', ShopTypeEnum::B2B->value);
    $shop = StoreShop::make()->action($this->organisation, $storeData);
    $organisation->refresh();

    $shopRoles       = Role::where('scope_type', 'Shop')->where('scope_id', $shop->id)->get();
    $shopPermissions = Permission::where('scope_type', 'Shop')->where('scope_id', $shop->id)->get();

    expect($shop)->toBeInstanceOf(Shop::class)
        ->and($organisation->marketStats->number_shops)->toBe(1)
        ->and($organisation->marketStats->number_shops_type_b2b)->toBe(1)
        ->and($shopRoles->count())->toBe(3)
        ->and($shopPermissions->count())->toBe(23);


    $user = $this->guest->user;
    $user->refresh();

    expect($user->getAllPermissions()->count())->toBe(26)
        ->and($user->hasAllRoles(["shop-admin-$shop->id"]))->toBe(true);


    return $shop;
});

test('create shop by command', function () {
    $organisation = $this->organisation;
    $this->artisan('shop:create', [
        'organisation'   => $organisation->slug,
        'name'           => 'Test Shop',
        'code'           => 'TEST',
        'type'           => ShopTypeEnum::FULFILMENT->value,
        '--warehouses'   => [$this->warehouse->id]
    ])->assertExitCode(0);
    $organisation->refresh();

    expect($organisation->marketStats->number_shops)->toBe(2)
        ->and($organisation->marketStats->number_shops_type_b2b)->toBe(1)
        ->and($organisation->marketStats->number_shops_type_fulfilment)->toBe(1);
})->depends('create shop');

test('update shop', function (Shop $shop) {
    $updateData = [
        'name' => 'Test Shop Updated',
    ];

    $shop = UpdateShop::make()->action($shop, $updateData);
    $shop->refresh();

    expect($shop->name)->toBe('Test Shop Updated');
})->depends('create shop');

test('seed shop permissions from command', function () {
    $this->artisan('shop:seed-permissions')->assertExitCode(0);
})->depends('create shop by command');

test('create website from command', function (Shop $shop) {
    $this->artisan('website:create', [
        'shop'   => $shop->slug,
        'domain' => 'test-hello.com',
        'code'   => 'test',
        'name'   => 'Test Website'
    ])->assertExitCode(0);
    $shop->refresh();
    expect($shop->website)->toBeInstanceOf(Website::class);
})->depends('create shop');


test('create department', function ($shop) {

    $departmentData = ProductCategory::factory()->definition();
    data_set($departmentData, 'type', ProductCategoryTypeEnum::DEPARTMENT->value);

    $department = StoreProductCategory::make()->action($shop, $departmentData);
    expect($department)->toBeInstanceOf(ProductCategory::class);

    return $department;
})->depends('create shop');

test('create sub department', function ($productCategory) {

    $subDepartmentData = ProductCategory::factory()->definition();
    data_set($subDepartmentData, 'type', ProductCategoryTypeEnum::SUB_DEPARTMENT->value);
    $subDepartment = StoreProductCategory::make()->action($productCategory, $subDepartmentData);
    expect($subDepartment)->toBeInstanceOf(ProductCategory::class);

    return $subDepartment;
})->depends('create department');

test('create second department', function ($shop) {
    $departmentData = ProductCategory::factory()->definition();
    data_set($departmentData, 'type', ProductCategoryTypeEnum::DEPARTMENT->value);

    $department = StoreProductCategory::make()->action($shop, $departmentData);
    expect($department)->toBeInstanceOf(ProductCategory::class);

    return $department;
})->depends('create shop');

test('update department', function ($department) {
    $newName    = 'Updated Department Name';
    $department = UpdateProductCategory::make()->action(
        $department,
        [
        'name' => $newName
    ]
    );

    expect($department->name)->toBe($newName);
    return $department;
})->depends('create department');

test('create family', function ($department) {
    $familyData = ProductCategory::factory()->definition();
    data_set($familyData, 'type', ProductCategoryTypeEnum::FAMILY->value);

    $family = StoreProductCategory::make()->action($department, $familyData);
    expect($family)->toBeInstanceOf(ProductCategory::class);

    return $department;
})->depends('update department');


test('create product', function ($shop) {

    $tradeUnits=[
        $this->tradeUnit->id=> [
            'units_per_main_outer'      => 1,
        ]
    ];

    $productData = array_merge(
        Product::factory()->definition(),
        [
            'trade_units'=> $tradeUnits,
            'price'      => 100,
        ]
    );

    $product     = StorePhysicalGood::make()->action($shop, $productData);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->tradeUnits()->count())->toBe(1)
        ->and($product->stats->number_outers)->toBe(1)
        ->and($product->stats->number_historic_outers)->toBe(1);

    return $product;
})->depends('create shop');

test('create product with many trade units', function ($shop) {

    $tradeUnits=[
        $this->tradeUnit->id=> [
            'units_per_main_outer'      => 1,
        ],
        $this->tradeUnit2->id=> [
            'units_per_main_outer'      => 1,
        ]
    ];

    $productData = array_merge(
        Product::factory()->definition(),
        [
            'trade_units'=> $tradeUnits,
            'price'      => 99,
        ]
    );


    $product     = StorePhysicalGood::make()->action($shop, $productData);


    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->unit_relationship_type)->toBe(ProductUnitRelationshipType::MULTIPLE)
        ->and($product->tradeUnits()->count())->toBe(2)
        ->and($product->stats->number_outers)->toBe(1)
        ->and($product->stats->number_historic_outers)->toBe(1);

    return $product;
})->depends('create shop');


test('update product', function ($product) {
    $productData = [
        'name' => 'Updated Product Name',
    ];
    $product = UpdateProduct::make()->action($product, $productData);

    expect($product->name)->toBe('Updated Product Name');
    return $product;
})->depends('create product');

test('add outer to product', function ($product) {


    expect($product->stats->number_outers)->toBe(1);

    $outerData =
        [
            'code'            => $product->code.'-v1',
            'main_outer_ratio'=> 2,
            'price'           => 99,
            'name'            => $product->name.' variant 1',
            'is_main'         => false
        ];


    $outer = StoreOuter::run($product, $outerData);
    $product->refresh();


    expect($outer)->toBeInstanceOf(Outer::class)
        ->and($outer->product->id)->toBe($product->id)
        ->and($outer->product->outers()->count())->toBe(2)
        ->and($product->stats->number_outers)->toBe(2)
        ->and($product->stats->number_historic_outers)->toBe(2);


    return $product;
})->depends('update product');

test('delete product', function ($product) {
    $product = DeleteProduct::run($product);

    $this->assertModelExists($product);
})->depends('create product');

test('create service', function ($shop) {



    $productData = array_merge(
        Product::factory()->definition(),
        [
            'type'       => ProductTypeEnum::SERVICE,
            'price'      => 100,
        ]
    );

    $product     = StoreNoPhysicalGood::make()->action($shop, $productData);

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->tradeUnits()->count())->toBe(0)
        ->and($product->stats->number_outers)->toBe(0)
        ->and($product->stats->number_historic_outers)->toBe(0);

    return $product;
})->depends('create shop');
