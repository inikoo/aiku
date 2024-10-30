<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-10-2024, Bali, Indonesia
 * Github: https://github.com/ganes556
 * Copyright: 2024
 *
 */

use App\Actions\Goods\Stock\StoreStock;
use App\Actions\Goods\StockFamily\StoreStockFamily;
use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Enums\UI\Inventory\LocationTabsEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->adminGuest = createAdminGuest($this->organisation->group);
    $this->group = group();

    $warehouse = Warehouse::first();
    if (!$warehouse) {
        data_set($storeData, "code", "CODE");
        data_set($storeData, "name", "NAME");

        $warehouse = StoreWarehouse::make()->action($this->organisation, $storeData);
    }
    $this->warehouse = $warehouse;

    $location = Location::first();
    if (!$location) {
        $location = StoreLocation::make()->action($warehouse, $storeData);
    }
    $this->location = $location;

    $stockFamily = StockFamily::first();
    if (!$stockFamily) {
        $arrayData = [
            'code' => 'ABC',
            'name' => 'ABC Stock'
        ];
        $stockFamily = StoreStockFamily::make()->action($this->group, $arrayData);
    }
    $this->stockFamily = $stockFamily;

    $stock = Stock::first();
    if (!$stock) {
        $stockData = Stock::factory()->definition();
        $stock = StoreStock::make()->action($this->group, $stockData);
    }
    $this->stock = $stock;

    $tradeUnit = TradeUnit::first();
    if (!$tradeUnit) {
        $tradeUnitData = TradeUnit::factory()->definition();
        $tradeUnit = StoreTradeUnit::make()->action($this->group, $tradeUnitData);
    }
    $this->tradeUnit = $tradeUnit;

    Config::set("inertia.testing.page_paths", [resource_path("js/Pages/Grp")]);
    actingAs($this->adminGuest->getUser());
});

test("UI Index locations", function () {
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

test("UI Index warehouse", function () {
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
                fn (AssertableInertia $page) => $page->where("title", 'SKUs families')->etc()
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