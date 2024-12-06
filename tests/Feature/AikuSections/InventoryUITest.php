<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 02 Dec 2024 15:02:07 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Analytics\GetSectionRoute;
use App\Actions\Goods\Stock\StoreStock;
use App\Actions\Goods\StockFamily\StoreStockFamily;
use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Actions\Inventory\WarehouseArea\StoreWarehouseArea;
use App\Enums\Analytics\AikuSection\AikuSectionEnum;
use App\Enums\UI\Inventory\LocationTabsEnum;
use App\Models\Analytics\AikuScopedSection;
use App\Models\Goods\TradeUnit;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB();
});

beforeEach(
    /**
     * @throws \Throwable
     */
    function () {
        $this->organisation = createOrganisation();
        $this->adminGuest   = createAdminGuest($this->organisation->group);
        $this->group        = group();

        $warehouse = Warehouse::first();
        $storeData = [
            'code' => 'CODE',
            'name' => 'NAME'
        ];


        if (!$warehouse) {
            $warehouse = StoreWarehouse::make()->action($this->organisation, $storeData);
        }
        $this->warehouse = $warehouse;

        $warehouseArea = WarehouseArea::first();
        if (!$warehouseArea) {
            $warehouseArea = StoreWarehouseArea::make()->action($warehouse, WarehouseArea::factory()->definition());
        }
        $this->warehouseArea = $warehouseArea;

        $location = Location::first();
        if (!$location) {
            $location = StoreLocation::make()->action($warehouse, $storeData);
        }
        $this->location = $location;

        $stockFamily = StockFamily::first();
        if (!$stockFamily) {
            $arrayData   = [
                'code' => 'ABC',
                'name' => 'ABC Stock'
            ];
            $stockFamily = StoreStockFamily::make()->action($this->group, $arrayData);
        }
        $this->stockFamily = $stockFamily;

        $stock = Stock::first();
        if (!$stock) {
            $stockData = Stock::factory()->definition();
            $stock     = StoreStock::make()->action($this->group, $stockData);
        }
        $this->stock = $stock;

        $tradeUnit = TradeUnit::first();
        if (!$tradeUnit) {
            $tradeUnitData = TradeUnit::factory()->definition();
            $tradeUnit     = StoreTradeUnit::make()->action($this->group, $tradeUnitData);
        }
        $this->tradeUnit = $tradeUnit;
        $this->artisan('group:seed_aiku_scoped_sections')->assertExitCode(0);

        Config::set("inertia.testing.page_paths", [resource_path("js/Pages/Grp")]);
        actingAs($this->adminGuest->getUser());
    }
);

test("UI Index locations", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.org.warehouses.show.infrastructure.locations.index", [
            $this->organisation->slug,
            $this->warehouse->slug,
        ])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Org/Warehouse/Locations")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", "locations")->etc()
            )
            ->has("tagRoute")
            ->has("tagsList")
            ->has("data");
    });
});

test("UI Create location", function () {
    $response = get(
        route("grp.org.warehouses.show.infrastructure.locations.create", [
            $this->organisation->slug,
            $this->warehouse->slug,
        ])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("CreateModel")
            ->has("title")
            ->has("breadcrumbs", 4)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", "new location")->etc()
            )
            ->has("formData");
    });
});

test("UI Show location", function () {
    $response = get(
        route("grp.org.warehouses.show.infrastructure.locations.show", [
            $this->organisation->slug,
            $this->warehouse->slug,
            $this->location->slug,
        ])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Org/Warehouse/Location")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", $this->location->slug)->etc()
            )
            ->has("navigation")
            ->has("tabs");
    });
});

test("UI Show location (showcase tab)", function () {
    $response = get(
        route("grp.org.warehouses.show.infrastructure.locations.show", [
            $this->organisation->slug,
            $this->warehouse->slug,
            $this->location->slug,
            "tabs" => LocationTabsEnum::SHOWCASE->value,
        ])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Org/Warehouse/Location")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", $this->location->slug)->etc()
            )
            ->has("navigation")
            ->has("tabs")
            ->has(LocationTabsEnum::SHOWCASE->value);
    });
});

test("UI Edit location", function () {
    $response = get(
        route("grp.org.warehouses.show.infrastructure.locations.edit", [
            $this->organisation->slug,
            $this->warehouse->slug,
            $this->location->slug,
        ])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("EditModel")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", $this->location->code)->etc()
            )
            ->has("navigation")
            ->has("formData");
    });
});


test("UI Index fulfilment locations", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.org.warehouses.show.fulfilment.locations.index", [
            $this->organisation->slug,
            $this->warehouse->slug,
        ])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Org/Warehouse/Fulfilment/Locations")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", "locations")->etc()
            )
            ->has("data");
    });
})->todo();

test("UI Show fulfilment location", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.org.warehouses.show.fulfilment.locations.show", [
            $this->organisation->slug,
            $this->warehouse->slug,
            $this->location->slug,
        ])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Org/Warehouse/Location")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", "location")->etc()
            )
            ->has("navigation")
            ->has("tabs");
    });
})->todo();

test("UI Index warehouses", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.org.warehouses.show.infrastructure.dashboard", [
            $this->organisation->slug,
            $this->warehouse->slug
        ])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Org/Warehouse/Warehouse")
            ->has("title")
            ->has("breadcrumbs", 2)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", $this->warehouse->name)->etc()
            )
            ->has("tabs")
            ->has("tagsList");
    });
});

test("UI Index warehouse areas", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.org.warehouses.show.infrastructure.warehouse_areas.index", [
            $this->organisation->slug,
            $this->warehouse->slug
        ])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Org/Warehouse/WarehouseAreas")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", 'warehouse areas')->etc()
            );
    });
});

test("UI Show warehouse area", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.org.warehouses.show.infrastructure.warehouse_areas.show", [
            $this->organisation->slug,
            $this->warehouse->slug,
            $this->warehouse->warehouseAreas->first()->slug

        ])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Org/Warehouse/WarehouseArea")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has('navigation')
            ->has('tabs')
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", $this->warehouseArea->name)->etc()
            );
    });
});


test("UI Index Org Stocks", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.org.warehouses.show.inventory.org_stocks.current_org_stocks.index", [
            $this->organisation->slug,
            $this->warehouse->slug
        ])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Org/Inventory/OrgStocks")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", 'Current SKUs')->etc()
            )
            ->has("data");
    });
});

test("UI Index Org Stock Families", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.org.warehouses.show.inventory.org_stock_families.index", [
            $this->organisation->slug,
            $this->warehouse->slug
        ])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Org/Inventory/OrgStockFamilies")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", 'SKU Families')->etc()
            )
            ->has("data");
    });
});

test("UI Index Stock Families", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.goods.stock-families.index")
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Goods/StockFamilies")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", 'SKU Families')->etc()
            )
            ->has("data");
    });
});

test("UI Create stock family", function () {
    $response = get(
        route("grp.goods.stock-families.create")
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("CreateModel")
            ->has("title")
            ->has("breadcrumbs", 4)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", "new SKU family")->etc()
            )
            ->has("formData");
    });
});

test("UI Edit stock family", function () {
    $response = get(
        route("grp.goods.stock-families.edit", [$this->stockFamily])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("EditModel")
            ->has("breadcrumbs", 3)
            ->has("title")
            ->has("navigation")
            ->has("formData", fn ($page) => $page->where("args", [
                'updateRoute' => [
                    'name'       => 'grp.models.stock-family.update',
                    'parameters' => $this->stockFamily->id
                ],
            ])->etc())
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", $this->stockFamily->name)->etc()
            )
            ->has("formData");
    });
});

test("UI Show Stock Family", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.goods.stock-families.show", [$this->stockFamily->slug])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Goods/StockFamily")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has('navigation')
            ->has('tabs')
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", $this->stockFamily->name)->etc()
            );
    });
});

test("UI Index Stocks", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.goods.stocks.index")
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Goods/Stocks")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has("pageHead")
            ->has("data");
    });
});

test("UI Show Stocks", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.goods.stocks.show", [$this->stock->slug])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Goods/Stock")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", $this->stock->slug)->etc()
            )
            ->has("tabs");
    });
});

test("UI Index Trade Units", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.goods.trade-units.index")
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Goods/TradeUnits")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has("pageHead")
            ->has("data");
    });
});

test("UI Show TradeUnit", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.goods.trade-units.show", [$this->tradeUnit->slug])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("Goods/TradeUnit")
            ->has("title")
            ->has("breadcrumbs", 3)
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", $this->tradeUnit->code)->etc()
            )
            ->has("tabs");
    });
});


test("UI Create Stock in Group", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.goods.stocks.create")
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("CreateModel")
            ->where("title", "new stock")
            ->has("breadcrumbs", 4)
            ->has('icon')
            ->has('formData', fn (AssertableInertia $page) => $page->where("route", [
                'name'       => 'grp.models.stock.store',
                'parameters' => []
            ])->etc())
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", 'new SKU')->etc()
            );
    });
});

test("UI Edit Stock in Group", function () {
    $this->withoutExceptionHandling();

    $response = get(
        route("grp.goods.stocks.edit", [$this->stock->slug])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("EditModel")
            ->where("title", "sku")
            ->has("breadcrumbs", 3)
            ->has('navigation')
            ->has('formData', fn (AssertableInertia $page) => $page->where("args", [
                'updateRoute' => [
                    'name'       => 'grp.models.stock.update',
                    'parameters' => $this->stock->id
                ],
            ])->etc())
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", $this->stock->name)->etc()
            );
    });
});


test("UI Create Stock in Stock Family Group", function () {
    $this->withoutExceptionHandling();
    $response = get(
        route("grp.goods.stock-families.show.stocks.create", [$this->stockFamily->slug])
    );
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component("CreateModel")
            ->where("title", "new stock")
            ->has("breadcrumbs", 5)
            ->has('icon')
            ->has('formData', fn (AssertableInertia $page) => $page->where("route", [
                'name'       => 'grp.models.stock-family.stock.store',
                'parameters' => [
                    'stockFamily' => $this->stockFamily->id
                ]
            ])->etc())
            ->has(
                "pageHead",
                fn (AssertableInertia $page) => $page->where("title", 'new SKU')->etc()
            );
    });
});

test('UI get section route inventory dashboard', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.warehouses.show.inventory.dashboard', [
        'organisation' => $this->organisation->slug,
        'warehouse'    => $this->warehouse->slug
    ]);
    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::INVENTORY->value)
        ->and($sectionScope->model_slug)->toBe($this->warehouse->slug);
});

test('UI get section route infrastructure index', function () {
    $sectionScope = GetSectionRoute::make()->handle("grp.org.warehouses.show.infrastructure.locations.index", [
        'organisation' => $this->organisation->slug,
        'warehouse'    => $this->warehouse->slug,
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::INVENTORY_INFRASTRUCTURE->value)
        ->and($sectionScope->model_slug)->toBe($this->warehouse->slug);
});

test('UI get section route incoming backlog', function () {
    $sectionScope = GetSectionRoute::make()->handle("grp.org.warehouses.show.incoming.backlog", [
        'organisation' => $this->organisation->slug,
        'warehouse'    => $this->warehouse->slug,
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::INVENTORY_INCOMING->value)
        ->and($sectionScope->model_slug)->toBe($this->warehouse->slug);
});

test('UI get section route org warehouses index', function () {
    $sectionScope = GetSectionRoute::make()->handle('grp.org.warehouses.index', [
        'organisation' => $this->organisation->slug,
    ]);

    expect($sectionScope)->toBeInstanceOf(AikuScopedSection::class)
        ->and($sectionScope->code)->toBe(AikuSectionEnum::ORG_WAREHOUSE->value)
        ->and($sectionScope->model_slug)->toBe($this->organisation->slug);
});
