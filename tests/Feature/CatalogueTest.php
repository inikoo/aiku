<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jan 2024 22:04:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Collection\StoreCollection;
use App\Actions\Catalogue\Collection\UpdateCollection;
use App\Actions\Catalogue\CollectionCategory\StoreCollectionCategory;
use App\Actions\Catalogue\CollectionCategory\UpdateCollectionCategory;
use App\Actions\Catalogue\Product\DeleteProduct;
use App\Actions\Catalogue\Product\StoreProduct;
use App\Actions\Catalogue\Product\UpdateProduct;
use App\Actions\Catalogue\ProductCategory\StoreProductCategory;
use App\Actions\Catalogue\ProductCategory\UpdateProductCategory;
use App\Actions\Catalogue\ProductVariant\StoreProductVariant;
use App\Actions\Catalogue\ProductVariant\UpdateProductVariant;
use App\Actions\Catalogue\Service\StoreService;
use App\Actions\Catalogue\Service\UpdateService;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Enums\Catalogue\Product\ProductUnitRelationshipType;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\CollectionCategory;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\ProductVariant;
use App\Models\Catalogue\Service;
use App\Models\Catalogue\Shop;
use App\Models\Goods\TradeUnit;
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

    if (!isset($this->tradeUnit)) {
        $this->tradeUnit = StoreTradeUnit::make()->action(
            $this->organisation->group,
            TradeUnit::factory()->definition()
        );
    }

    if (!isset($this->tradeUnit2)) {
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
        ->and($shopRoles->count())->toBe(9)
        ->and($shopPermissions->count())->toBe(23);


    $user = $this->guest->user;
    $user->refresh();

    expect($user->getAllPermissions()->count())->toBe(25)
        ->and($user->hasAllRoles(["shop-admin-$shop->id"]))->toBeTrue();


    return $shop;
});

test('create shop by command', function () {
    $organisation = $this->organisation;
    $this->artisan('shop:create', [
        'organisation' => $organisation->slug,
        'name'         => 'Test Shop',
        'code'         => 'TEST',
        'type'         => ShopTypeEnum::FULFILMENT->value,
        '--warehouses' => [$this->warehouse->id]
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


test('create physical good product', function ($shop) {
    $tradeUnits = [
        $this->tradeUnit->id => [
            'units' => 1,
        ]
    ];

    $productData = array_merge(
        Product::factory()->definition(),
        [
            'trade_units' => $tradeUnits,
            'price'       => 100,
            'unit'        => 'unit'
        ]
    );

    $product = StoreProduct::make()->action($shop, $productData);
    $product->refresh();

    $productVariant=$product->productVariant;

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->asset)->toBeInstanceOf(Asset::class)
        ->and($product->tradeUnits()->count())->toBe(1)
        ->and($shop->organisation->marketStats->number_products)->toBe(1)
        ->and($shop->organisation->marketStats->number_assets_type_product)->toBe(1)
        ->and($shop->organisation->marketStats->number_assets_type_service)->toBe(0)
        ->and($shop->group->marketStats->number_products)->toBe(1)
        ->and($shop->group->marketStats->number_assets_type_product)->toBe(1)
        ->and($shop->stats->number_products)->toBe(1)
        ->and($shop->stats->number_assets_type_product)->toBe(1)
        ->and($productVariant)->toBeInstanceOf(ProductVariant::class)
        ->and($productVariant->name)->toBe($product->name)
        ->and($productVariant->stats->number_historic_product_variants)->toBe(1);



    return $product;
})->depends('create shop');

test('create physical good product with many trade units', function ($shop) {
    $tradeUnits = [
        $this->tradeUnit->id  => [
            'units' => 1,
        ],
        $this->tradeUnit2->id => [
            'units' => 1,
        ]
    ];

    $productData = array_merge(
        Product::factory()->definition(),
        [
            'trade_units' => $tradeUnits,
            'price'       => 99,
            'unit'        => 'pack'
        ]
    );


    $product = StoreProduct::make()->action($shop, $productData);
    $shop->refresh();

    expect($product)->toBeInstanceOf(Product::class)
        ->and($product->unit_relationship_type)->toBe(ProductUnitRelationshipType::MULTIPLE)
        ->and($product->tradeUnits()->count())->toBe(2)
        ->and($shop->stats->number_products)->toBe(2)
        ->and($product->stats->number_historic_assets)->toBe(1)
        ->and($shop->group->marketStats->number_products)->toBe(2)
        ->and($shop->group->marketStats->number_assets_type_product)->toBe(2)
        ->and($shop->organisation->marketStats->number_products)->toBe(2)
        ->and($shop->organisation->marketStats->number_assets_type_product)->toBe(2);

    return $product;
})->depends('create shop');

test('update product', function (Product $product) {
    expect($product->name)->not->toBe('Updated Asset Name');
    $productData = [
        'name'        => 'Updated Asset Name',
        'description' => 'Updated Asset Description',
        'rrp'         => 99.99
    ];
    $product     = UpdateProduct::make()->action($product, $productData);
    $product->refresh();
    /** @var Asset $asset */
    $asset = $product->asset;

    expect($product->name)->toBe('Updated Asset Name')
        ->and($product->stats->number_historic_assets)->toBe(2)
        ->and($asset->stats->number_historic_assets)->toBe(2)
        ->and($asset->name)->toBe('Updated Asset Name')
        ->and($product->name)->toBe('Updated Asset Name');

    return $product;
})->depends('create physical good product');

test('add variant to product', function (Product $product) {
    expect($product->stats->number_product_variants)->toBe(1);


    $outerData =
        [
            'code'    => $product->code.'-v1',
            'ratio'   => 2,
            'price'   => 99,
            'name'    => $product->name.' variant 1',
            'is_main' => false
        ];


    $productVariant = StoreProductVariant::run($product, $outerData);
    $product->refresh();


    expect($productVariant)->toBeInstanceOf(ProductVariant::class)
        ->and($productVariant->is_main)->toBeFalse()
        ->and($productVariant->product->id)->toBe($product->id)
        ->and($product->stats->number_product_variants)->toBe(2)
        ->and($product->stats->number_historic_assets)->toBe(2)
        ->and($productVariant->stats->number_historic_product_variants)->toBe(1);



    return $productVariant;
})
    ->depends('update product');

test('update second product variant', function (ProductVariant $productVariant) {
    $product = $productVariant->product;
    expect($productVariant->id)->not->toBe($product->product_variant_id)
        ->and($product->stats->number_product_variants)->toBe(2);
    $modelData = [
        'name'  => 'Updated Product Sec Name',
        'code'  => 'sec_code',
        'price' => 99.99
    ];

    $productVariant = UpdateProductVariant::run($productVariant, $modelData);
    $productVariant->refresh();
    $product->refresh();

    expect($productVariant->name)->toBe('Updated Product Sec Name')
        ->and($productVariant->code)->toBe('sec_code')
        ->and($product->stats->number_product_variants)->toBe(2)
        ->and($productVariant->stats->number_historic_product_variants)->toBe(2);

    return $product;
})->depends('add variant to product');


test('delete product', function ($product) {
    $shop = $product->shop;


    expect($shop->stats->number_products)->toBe(2)
        ->and($shop->group->marketStats->number_products)->toBe(2)
        ->and($shop->organisation->marketStats->number_products)->toBe(2);

    DeleteProduct::run($product);
    $shop->refresh();

    expect($shop->stats->number_products)->toBe(1)
        ->and($shop->group->marketStats->number_products)->toBe(1)
        ->and($shop->organisation->marketStats->number_products)->toBe(1);

    return $shop;
})->depends('create physical good product');

test('create service', function (Shop $shop) {
    $serviceData = array_merge(
        Service::factory()->definition(),
        [
            'price' => 100,
            'unit'  => 'job',
        ]
    );

    $service = StoreService::make()->action($shop, $serviceData);
    $shop->refresh();
    $group        = $shop->group;
    $organisation = $shop->organisation;
    $asset        = $service->asset;

    expect($service)->toBeInstanceOf(Service::class)
        ->and($asset)->toBeInstanceOf(Asset::class)
        ->and($service->stats->number_historic_assets)->toBe(1)
        ->and($group->marketStats->number_assets)->toBe(2)
        ->and($group->marketStats->number_products)->toBe(1)
        ->and($group->marketStats->number_services)->toBe(1)
        ->and($group->marketStats->number_assets_type_product)->toBe(1)
        ->and($group->marketStats->number_assets_type_service)->toBe(1)
        ->and($organisation->marketStats->number_products)->toBe(1)
        ->and($organisation->marketStats->number_assets_type_service)->toBe(1)
        ->and($shop->stats->number_assets)->toBe(2)
        ->and($shop->stats->number_products)->toBe(1)
        ->and($shop->stats->number_assets_type_product)->toBe(1)
        ->and($shop->stats->number_assets_type_service)->toBe(1);

    return $service;
})->depends('delete product');

test('update service', function (Service $service) {
    expect($service->name)->not->toBe('Updated Service Name');
    $productData = [
        'name'        => 'Updated Service Name',
        'description' => 'Updated Service Description',
        'rrp'         => 99.99
    ];
    $service     = UpdateService::make()->action(service: $service, modelData: $productData);

    $service->refresh();

    expect($service->asset->name)->toBe('Updated Service Name')
        ->and($service->asset->stats->number_historic_assets)->toBe(2)
        ->and($service->stats->number_historic_assets)->toBe(2);

    return $service;
})->depends('create service');

test('create collection category', function ($shop) {
    $collectionCategory = StoreCollectionCategory::make()->action(
        $shop,
        [
            'code'        => 'AAA',
            'name'        => 'Cat one aaa',
            'description' => 'Cat one aaa description'
        ]
    );
    $shop->refresh();
    expect($collectionCategory)->toBeInstanceOf(CollectionCategory::class)
        ->and($shop->stats->number_collection_categories)->toBe(1)
        ->and($shop->organisation->marketStats->number_collection_categories)->toBe(1)
        ->and($shop->group->marketStats->number_collection_categories)->toBe(1);


    return $collectionCategory;
})->depends('create shop');

test('create collection', function ($shop) {
    $collectionCategory = StoreCollection::make()->action(
        $shop,
        [
            'code'        => 'MyFColl',
            'name'        => 'My first collection',
            'description' => 'My first collection description'
        ]
    );
    $shop->refresh();
    expect($collectionCategory)->toBeInstanceOf(Collection::class)
        ->and($shop->stats->number_collections)->toBe(1)
        ->and($shop->organisation->marketStats->number_collections)->toBe(1)
        ->and($shop->group->marketStats->number_collections)->toBe(1);


    return $collectionCategory;
})->depends('create shop');

test('create collection in a collection category', function (CollectionCategory $collectionCategory) {
    $collection = StoreCollection::make()->action(
        $collectionCategory,
        [
            'code'        => 'BBB',
            'name'        => 'Cat two bbb',
            'description' => 'Cat two bbb description'
        ]
    );

    $collectionCategory->refresh();
    expect($collection)->toBeInstanceOf(Collection::class)
        ->and($collectionCategory->stats->number_collections)->toBe(1)
        ->and($collectionCategory->shop->stats->number_collections)->toBe(2)
        ->and($collectionCategory->organisation->marketStats->number_collections)->toBe(2)
        ->and($collectionCategory->group->marketStats->number_collections)->toBe(2);


    return $collection;
})->depends('create collection category');

test('update collection', function ($collection) {
    expect($collection->name)->not->toBe('Updated Collection Name');

    $collectionData = [
        'name'        => 'Updated Collection Name',
        'description' => 'Updated Collection Description',
    ];
    $collection     = UpdateCollection::make()->action($collection, $collectionData);

    expect($collection->name)->toBe('Updated Collection Name');

    return $collection;
})->depends('create collection');

test('update collection category', function ($collectionCategory) {
    expect($collectionCategory->name)->not->toBe('Updated Collection Category Name');

    $collectionCategoryData = [
        'name'        => 'Updated Collection Category Name',
        'description' => 'Updated Collection Category Description',
    ];
    $collectionCategory     = UpdateCollectionCategory::make()->action($collectionCategory, $collectionCategoryData);

    expect($collectionCategory->name)->toBe('Updated Collection Category Name');

    return $collectionCategory;
})->depends('create collection category');