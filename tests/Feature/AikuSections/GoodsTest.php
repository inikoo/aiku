<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 03 Dec 2024 20:13:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */
/** @noinspection PhpUnhandledExceptionInspection */
use App\Actions\Goods\Stock\StoreStock;
use App\Actions\Goods\Stock\UpdateStock;
use App\Actions\Goods\StockFamily\DeleteStockFamily;
use App\Actions\Goods\StockFamily\StoreStockFamily;
use App\Actions\Goods\StockFamily\UpdateStockFamily;
use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Enums\SupplyChain\StockFamily\StockFamilyStateEnum;
use App\Models\Goods\TradeUnit;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;

beforeAll(function () {
    loadDB();
});


beforeEach(function () {
    $this->group = createGroup();
});


test('create stock family', function () {
    $stockFamily = StoreStockFamily::make()->action(
        $this->group,
        StockFamily::factory()->definition()
    );

    expect($stockFamily)->toBeInstanceOf($stockFamily::class)
        ->and($this->group->goodsStats->number_stock_families)->toBe(1)
        ->and($this->group->goodsStats->number_current_stock_families)->toBe(0);

    return $stockFamily;
});

test('update stock family', function (StockFamily $stockFamily) {
    $stockFamily = UpdateStockFamily::make()->action(
        $stockFamily,
        [
            'code' => 'A0001',
            'name' => 'Updated Stock Family Name'
        ]
    );

    expect($stockFamily->code)->toBe('A0001')
        ->and($stockFamily->name)->toBe('Updated Stock Family Name');

    return $stockFamily;
})->depends('create stock family');


test('create stock as draft', function () {

    $stockData = Stock::factory()->definition();
    $stock = StoreStock::make()->action($this->group, $stockData);

    $tradeUnit = $this->group->tradeUnits->first();

    expect($stock)->toBeInstanceOf(Stock::class)
        ->and($this->group->goodsStats->number_trade_units)->toBe(1)
        ->and($this->group->goodsStats->number_stocks)->toBe(1)
        ->and($this->group->goodsStats->number_stocks_state_in_process)->toBe(1)
        ->and($this->group->goodsStats->number_current_stocks)->toBe(0)
        ->and($tradeUnit)->toBeInstanceOf(TradeUnit::class)
        ->and($tradeUnit->code)->toBe($stockData['code'])
        ->and($tradeUnit->name)->toBe($stockData['name']);

    return $stock->fresh();
});

test('create stock in stock family', function (StockFamily $stockFamily) {
    $stockData = Stock::factory()->definition();
    data_set($stockData, 'state', StockStateEnum::ACTIVE);
    $stock = StoreStock::make()->action($stockFamily, $stockData);

    expect($stock)->toBeInstanceOf(Stock::class)
        ->and($stockFamily->state)->toBe(StockFamilyStateEnum::IN_PROCESS)
        ->and($this->group->goodsStats->number_stocks)->toBe(2)
        ->and($this->group->goodsStats->number_stocks_state_in_process)->toBe(1)
        ->and($this->group->goodsStats->number_current_stocks)->toBe(1)
        ->and($this->group->goodsStats->number_stock_families)->toBe(1)
        ->and($this->group->goodsStats->number_current_stock_families)->toBe(1);


    return $stock->fresh();
})->depends('create stock family');

test('activate draft stock', function (Stock $stock) {

    UpdateStock::make()->action(
        $stock,
        [
            'state' => StockStateEnum::ACTIVE
        ]
    );

    expect($stock->state)->toBe(StockStateEnum::ACTIVE)
        ->and($this->group->goodsStats->number_stocks_state_in_process)->toBe(0)
        ->and($this->group->goodsStats->number_current_stocks)->toBe(2);

    return $stock;
})->depends('create stock as draft');

test('delete stock family', function ($stockFamily) {
    $deletedStockFamily = DeleteStockFamily::make()->action($stockFamily);

    expect(StockFamily::find($deletedStockFamily->id))->toBeNull();

    return $deletedStockFamily;

})->depends('create stock family');
