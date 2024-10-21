<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 21-10-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

use App\Actions\Catalogue\MasterProductCategory\StoreMasterProductCategory;
use App\Actions\Catalogue\MasterShop\StoreMasterShop;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\MasterShop;

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

    // $stocks          = createStocks($this->group);
    // $orgStocks       = createOrgStocks($this->organisation, $stocks);
    // $this->orgStock1 = $orgStocks[0];
    // $this->orgStock2 = $orgStocks[1];


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
            'code' => 'sh1',
            'name' => 'test master shop',
            'type' => ProductCategoryTypeEnum::DEPARTMENT,
        ]
    );


    dd($masterShop);

    $masterProductCategory->refresh();

    // Assertions to ensure the category was created correctly
    expect($masterProductCategory)->not->toBeNull();
    expect($masterProductCategory->code)->toBe('ts12');
    expect($masterProductCategory->name)->toBe('Test Category');
    expect($masterProductCategory->group_id)->toBe($this->group->id);
    expect($masterProductCategory->type)->toBe(ProductCategoryTypeEnum::DEPARTMENT);


    return null;
});

test('create master product category', function (MasterShop $masterShop) {
    $masterProductCategory = StoreMasterProductCategory::make()->action(
        $masterShop,
        [
            'code' => 'ts12',
            'name' => 'testName',
            'type' => ProductCategoryTypeEnum::DEPARTMENT,
        ]
    );


    // dd($masterProductCategory);

    $masterProductCategory->refresh();

    // Assertions to ensure the category was created correctly
    expect($masterProductCategory)->not->toBeNull();
    expect($masterProductCategory->code)->toBe('ts12');
    expect($masterProductCategory->name)->toBe('Test Category');
    expect($masterProductCategory->group_id)->toBe($this->group->id);
    expect($masterProductCategory->type)->toBe(ProductCategoryTypeEnum::DEPARTMENT);


    return null;
})->depends("create master shop");
