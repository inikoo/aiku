<?php
/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 24 Jun 2024 13:27:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Manufacturing\Production\StoreProduction;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Inventory\Location;
use App\Models\Manufacturing\Production;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {

    $this->organisation = createOrganisation();
    $this->adminGuest   = createAdminGuest($this->organisation->group);

    $production = Production::first();
    if (!$production) {
        data_set($storeData, 'code', 'CODE');
        data_set($storeData, 'name', 'NAME');

        $production = StoreProduction::make()->action(
            $this->organisation,
            $storeData
        );
    }
    $this->production = $production;
    
    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->user);
});

test('UI Index raw materials', function () {
    $response = $this->get(route('grp.org.productions.show.crafts.raw_materials.index', [$this->organisation->slug, $this->production->slug]));

    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Manufacturing/RawMaterials')
            ->has('title')
            ->has('tabs')
            ->has('breadcrumbs', 3);
    });
});
