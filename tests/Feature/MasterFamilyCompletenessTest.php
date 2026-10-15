<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Catalogue\ProductCategory\StoreProductCategory;
use App\Actions\Masters\MasterProductCategory\StoreMasterDepartment;
use App\Actions\Masters\MasterProductCategory\StoreMasterFamily;
use App\Actions\Masters\MasterProductCategory\UI\GetMasterFamilyCompleteness;
use App\Actions\Masters\MasterProductCategory\UI\GetMasterFamilyShopsStatus;
use App\Actions\Masters\MasterShop\StoreMasterShop;
use App\Enums\Catalogue\MasterProductCategory\MasterProductCategoryTypeEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\ProductCategory;

use function Pest\Laravel\actingAs;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    $this->group      = createGroup();
    $this->adminGuest = createAdminGuest($this->group);
    list($this->organisation, $this->user, $this->shop) = createShop();
    actingAs($this->adminGuest->getUser());

    $masterShop = StoreMasterShop::make()->action($this->group, [
        'type' => ShopTypeEnum::B2B,
        'code' => 'MC'.substr(uniqid(), -6),
        'name' => 'Completeness Master Shop',
    ]);

    $masterDepartment = StoreMasterDepartment::make()->action($masterShop, [
        'code' => 'MCD-'.uniqid(),
        'name' => 'completeness department',
        'type' => MasterProductCategoryTypeEnum::DEPARTMENT,
    ]);

    $this->masterFamily = StoreMasterFamily::make()->action($masterDepartment, [
        'code' => 'MCF-'.uniqid(),
        'name' => 'completeness family',
        'type' => MasterProductCategoryTypeEnum::FAMILY,
    ]);
});

test('master completeness lists what the master family provides and scores it', function () {
    $this->masterFamily->updateQuietly([
        'description_title'          => 'A title',
        'description'                => '<p>Top description</p>',
        'description_extra'          => '<p> </p>',
        'gr_vol_discount_quantity'   => 3,
        'gr_vol_discount_percentage' => 10,
        'faq'                        => [
            ['question' => 'First?', 'answer' => 'Yes'],
            ['question' => 'Second?', 'answer' => 'No'],
            ['question' => '', 'answer' => 'Ignored'],
        ],
    ]);

    $completeness = GetMasterFamilyCompleteness::run($this->masterFamily->fresh());

    $items = collect($completeness['items'])->keyBy('key');

    expect($completeness['total'])->toBe(9)
        ->and($items->where('is_complete', false)->keys()->sort()->values()->all())->toBe(['description_extra', 'sub_department'])
        ->and($items['faq']['label'])->toBe('FAQ (2 questions)')
        ->and($completeness['complete'])->toBe(7)
        ->and($completeness['score'])->toBe(78);
});

test('shops status groups the shop families of the master family by state', function () {
    $this->shop->updateQuietly(['state' => ShopStateEnum::OPEN]);

    $familyData = ProductCategory::factory()->definition();
    data_set($familyData, 'type', ProductCategoryTypeEnum::FAMILY->value);
    data_set($familyData, 'master_product_category_id', $this->masterFamily->id);
    $shopFamily = StoreProductCategory::make()->action($this->shop, $familyData);
    $shopFamily->updateQuietly(['state' => ProductCategoryStateEnum::DISCONTINUING]);

    $shopsStatus = GetMasterFamilyShopsStatus::run($this->masterFamily);

    expect($shopsStatus['total'])->toBe(1)
        ->and($shopsStatus['states'])->toBe([
            [
                'state' => ProductCategoryStateEnum::DISCONTINUING->value,
                'label' => 'Discontinuing',
                'count' => 1,
                'shops' => [$this->shop->code],
            ],
        ]);
});
