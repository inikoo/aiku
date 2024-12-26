<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 21-10-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

/** @noinspection PhpUnhandledExceptionInspection */


use App\Actions\Catalogue\MasterProductCategory\StoreMasterProductCategory;
use App\Actions\Catalogue\MasterProductCategory\UpdateMasterProductCategory;
use App\Actions\Catalogue\MasterShop\StoreMasterShop;
use App\Actions\Catalogue\MasterShop\UpdateMasterShop;
use App\Enums\Catalogue\MasterProductCategory\MasterProductCategoryTypeEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Goods\MasterProductCategory;
use App\Models\Goods\MasterShop;

use function Pest\Laravel\actingAs;

uses()->group('base');

beforeAll(function () {
    loadDB();
});


beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->guest        = createAdminGuest($this->organisation->group);
    $this->adminGuest   = createAdminGuest($this->organisation->group);
    $this->group        = $this->organisation->group;

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->getUser());
    setPermissionsTeamId($this->organisation->group->id);
});

test('create master shop', function () {
    $masterShop = StoreMasterShop::make()->action(
        $this->group,
        [
            'code' => "SHOP1",
            'name' => "shop1",
            'type' => ShopTypeEnum::DROPSHIPPING
        ]
    );

    $masterShop->refresh();

    expect($masterShop)->toBeInstanceOf(MasterShop::class)
        ->and($masterShop)->not->toBeNull()
        ->and($masterShop->code)->toBe('SHOP1')
        ->and($masterShop->name)->toBe('shop1')
        ->and($masterShop->group_id)->toBe($this->group->id)
        ->and($masterShop->type)->toBe(ShopTypeEnum::DROPSHIPPING)
        ->and($masterShop->status)->toBeTrue();

    return $masterShop;
});


test('update master shop', function (MasterShop $masterShop) {
    $updatedMasterShop = UpdateMasterShop::make()->action(
        $masterShop,
        [
            'name' => "shop2",
            'type' => ShopTypeEnum::FULFILMENT,
        ]
    );

    $updatedMasterShop->refresh();

    expect($updatedMasterShop)->toBeInstanceOf(MasterShop::class)
        ->and($updatedMasterShop)->not->toBeNull()
        ->and($updatedMasterShop->name)->toBe('shop2')
        ->and($updatedMasterShop->type)->toBe(ShopTypeEnum::FULFILMENT);
})->depends('create master shop');

test('create master product category', function (MasterShop $masterShop) {
    $masterProductCategory = StoreMasterProductCategory::make()->action(
        $masterShop,
        [
            'code' => 'PRODUCT_CATEGORY1',
            'name' => 'product category 1',
            'type' => MasterProductCategoryTypeEnum::DEPARTMENT
        ]
    );

    $masterProductCategory->refresh();

    expect($masterProductCategory)->toBeInstanceOf(MasterProductCategory::class)
        ->and($masterProductCategory)->not->toBeNull()
        ->and($masterProductCategory->code)->toBe('PRODUCT_CATEGORY1')
        ->and($masterProductCategory->name)->toBe('product category 1')
        ->and($masterProductCategory->master_shop_id)->toBe($masterShop->id)
        ->and($masterProductCategory->group_id)->toBe($this->group->id)
        ->and($masterProductCategory->type)->toBe(MasterProductCategoryTypeEnum::DEPARTMENT);

    return $masterProductCategory;
})->depends("create master shop");

test('update master product category', function (MasterProductCategory $masterProductCategory) {
    $updatedMasterProductCategory = UpdateMasterProductCategory::make()->action(
        $masterProductCategory,
        [
            'code' => 'PRODUCT_CATEGORY2',
            'name' => 'product category 2',
        ]
    );

    $updatedMasterProductCategory->refresh();

    expect($updatedMasterProductCategory)->toBeInstanceOf(MasterProductCategory::class)
        ->and($updatedMasterProductCategory)->not->toBeNull()
        ->and($updatedMasterProductCategory->code)->toBe('PRODUCT_CATEGORY2')
        ->and($updatedMasterProductCategory->name)->toBe('product category 2');
})->depends("create master product category");
