<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Catalogue\ProductCategory\StoreProductCategory;
use App\Actions\Catalogue\ProductCategory\StoreProductCategoryWebpage;
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
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

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

test('families in shop page shows each shop family status, workflow and page score', function () {
    $this->shop->updateQuietly(['state' => ShopStateEnum::OPEN]);

    $familyData = ProductCategory::factory()->definition();
    data_set($familyData, 'type', ProductCategoryTypeEnum::FAMILY->value);
    data_set($familyData, 'master_product_category_id', $this->masterFamily->id);
    $shopFamily = StoreProductCategory::make()->action($this->shop, $familyData);
    $shopFamily->updateQuietly(['state' => ProductCategoryStateEnum::DISCONTINUING, 'webpage_id' => null]);

    get(route('grp.masters.master_shops.show.master_families.families', [$this->masterFamily->masterShop->slug, $this->masterFamily->slug]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Org/Catalogue/Families')
            ->where('shops_status.states.0.state', ProductCategoryStateEnum::DISCONTINUING->value)
            ->where('queryBuilderProps.index.elementGroups.state.elements', [
                ProductCategoryStateEnum::DISCONTINUING->value => ['Discontinuing', 1],
            ])
            ->has('index.data', 1)
            ->where('index.data.0.shop_status', ['state' => ProductCategoryStateEnum::DISCONTINUING->value, 'label' => 'Discontinuing'])
            ->where('index.data.0.workflow.current', 1)
            ->where('index.data.0.workflow.steps.0', ['key' => 'created', 'label' => 'Page created', 'done' => false])
            ->has('index.data.0.workflow.steps', 4)
            ->where('index.data.0.page_score', null)
            ->where('index.data.0.public_url', null)
            ->etc());
});

test('families in shop page scores shop families that have a webpage', function () {
    $familyData = ProductCategory::factory()->definition();
    data_set($familyData, 'type', ProductCategoryTypeEnum::FAMILY->value);
    data_set($familyData, 'master_product_category_id', $this->masterFamily->id);
    $shopFamily = StoreProductCategory::make()->action($this->shop, $familyData);
    $shopFamily->webpage ?? StoreProductCategoryWebpage::make()->action($shopFamily);

    get(route('grp.masters.master_shops.show.master_families.families', [$this->masterFamily->masterShop->slug, $this->masterFamily->slug]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Org/Catalogue/Families')
            ->has('index.data', 1)
            ->whereType('index.data.0.page_score', 'integer')
            ->etc());
});

test('edit master family page shows the master content, parent options and checklist, and saves fields together', function () {
    $masterFamily     = $this->masterFamily->fresh();
    $masterDepartment = $masterFamily->masterDepartment;

    get(route('grp.masters.master_shops.show.master_departments.show.master_families.edit', [$masterFamily->masterShop->slug, $masterDepartment->slug, $masterFamily->slug]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Masters/EditMasterFamily')
            ->where('masterFamily.code', $masterFamily->code)
            ->where('masterFamily.master_department_id', $masterDepartment->id)
            ->where('departments', fn ($departments) => collect($departments)->contains('id', $masterDepartment->id))
            ->where('completeness.total', 9)
            ->where('familiesRoute.name', 'grp.masters.master_shops.show.master_departments.show.master_families.families')
            ->where('updateRoute.name', 'grp.models.master_product_category.update')
            ->etc());

    $this->patch(route('grp.models.master_product_category.update', $masterFamily->id), [
        'name'              => 'Renamed family',
        'description_title' => 'New title',
        'master_department_or_master_sub_department_id' => $masterDepartment->id,
    ])->assertSessionHasNoErrors();

    $masterFamily->refresh();

    expect($masterFamily->name)->toBe('Renamed family')
        ->and($masterFamily->description_title)->toBe('New title')
        ->and($masterFamily->master_department_id)->toBe($masterDepartment->id);
});
