<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:03:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Procurement\OrgAgent\StoreOrgAgent;
use App\Actions\Procurement\OrgAgent\UpdateOrgAgent;
use App\Actions\Procurement\OrgSupplier\StoreOrgSupplier;
use App\Actions\Procurement\OrgSupplier\UpdateOrgSupplier;
use App\Actions\SupplyChain\Agent\DeleteAgent;
use App\Actions\SupplyChain\Agent\StoreAgent;
use App\Actions\SupplyChain\Agent\UpdateAgent;
use App\Actions\SupplyChain\Supplier\DeleteSupplier;
use App\Actions\SupplyChain\Supplier\StoreSupplier;
use App\Actions\SupplyChain\Supplier\UpdateSupplier;
use App\Actions\SupplyChain\SupplierProduct\StoreSupplierProduct;
use App\Models\Goods\TradeUnit;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgAgentStats;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\OrgSupplierStats;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;

beforeAll(function () {
    loadDB();
});


beforeEach(function () {
    $this->organisation    = createOrganisation();
    $this->group           = group();
    $this->stocks          = createStocks($this->group);

});

test('create agent', function () {
    $modelData = Agent::factory()->definition();
    $agent     = StoreAgent::make()->action(
        group: $this->group,
        modelData: $modelData
    );

    expect($agent)->toBeInstanceOf(Agent::class)
        ->and($this->group->supplyChainStats->number_agents)->toBe(1)
        ->and($this->group->supplyChainStats->number_archived_agents)->toBe(0);



    return $agent;
});

test('update agent', function (Agent $agent) {
    $modelData = [
        'name' => 'UpdatedName'
    ];
    $updatedAgent     = UpdateAgent::make()->action(
        agent: $agent,
        modelData: $modelData
    );

    expect($updatedAgent)->toBeInstanceOf(Agent::class)
        ->and($updatedAgent->name)->toBe('UpdatedName');

    return $updatedAgent;
})->depends('create agent');


test('create another agent', function () {
    $modelData = Agent::factory()->definition();
    $agent     = StoreAgent::make()->action(
        group: $this->group,
        modelData: $modelData
    );

    expect($agent)->toBeInstanceOf(Agent::class)
        ->and($this->group->supplyChainStats->number_agents)->toBe(2)
        ->and($this->group->supplyChainStats->number_archived_agents)->toBe(0);

    return $agent;
});


test('create independent supplier', function () {
    $supplier = StoreSupplier::make()->action(
        parent: $this->group,
        modelData: Supplier::factory()->definition()
    );
    expect($supplier)->toBeInstanceOf(Supplier::class)
        ->and($supplier->agent_id)->toBeNull()
        ->and($this->group->supplyChainStats->number_suppliers)->toBe(1);


    return $supplier;
});

test('update supplier', function (Supplier $supplier) {
    $modelData = [
        'contact_name' => 'UpdatedName'
    ];
    $updatedSupplier     = UpdateSupplier::make()->action(
        supplier: $supplier,
        modelData: $modelData
    );

    expect($updatedSupplier)->toBeInstanceOf(Supplier::class)
        ->and($updatedSupplier->contact_name)->toBe('UpdatedName');

    return $updatedSupplier;
})->depends('create independent supplier');

test('create independent supplier 2', function () {
    $supplier = StoreSupplier::make()->action(
        parent: $this->group,
        modelData: Supplier::factory()->definition(),
    );
    expect($supplier)->toBeInstanceOf(Supplier::class)
        ->and($supplier->agent_id)->toBeNull()
        ->and($this->group->supplyChainStats->number_suppliers)->toBe(2);


    return $supplier;
});

test('number independent supplier should be two', function () {
    $this->assertEquals(2, $this->group->supplyChainStats->number_suppliers);
});

test('create supplier in agent', function ($agent) {
    expect($agent->stats->number_suppliers)->toBe(0);

    $supplier = StoreSupplier::make()->action(
        parent: $agent,
        modelData: Supplier::factory()->definition()
    );
    $agent->refresh();
    expect($supplier)->toBeInstanceOf(Supplier::class)
        ->and($agent->stats->number_suppliers)->toBe(1);

    return $supplier;
})->depends('create agent');

test('create supplier product independent supplier', function ($supplier) {

    $supplierProductData = SupplierProduct::factory()->definition();
    data_set($supplierProductData, 'stock_id', $this->stocks[0]->id);
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, $supplierProductData);
    $this->assertModelExists($supplierProduct);

    return $supplierProduct;
})->depends('create independent supplier');

test('create supplier product independent supplier 2', function ($supplier) {
    $supplierProductData = SupplierProduct::factory()->definition();
    data_set($supplierProductData, 'stock_id', $this->stocks[1]->id);
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, $supplierProductData);
    $this->assertModelExists($supplierProduct);

    return $supplierProduct;
})->depends('create independent supplier');

test('create supplier product in agent supplier', function ($supplier) {
    $supplierProductData = SupplierProduct::factory()->definition();
    data_set($supplierProductData, 'stock_id', $this->stocks[2]->id);
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, $supplierProductData);
    expect($supplierProduct)->toBeInstanceOf(SupplierProduct::class);
})->depends('create supplier in agent');


test('create trade unit', function () {
    $tradeUnit = StoreTradeUnit::make()->action(
        $this->group,
        TradeUnit::factory()->definition()
    );
    $this->assertModelExists($tradeUnit);

    return $tradeUnit;
});

test('create org-agent', function ($agent) {
    $orgAgent = StoreOrgAgent::make()->action(
        $this->organisation,
        $agent,
        []
    );

    expect($orgAgent)->toBeInstanceOf(OrgAgent::class)
        ->and($orgAgent->stats)->toBeInstanceOf(OrgAgentStats::class);

    return $orgAgent;
})->depends('create agent');

test('update org-agent', function ($orgAgent) {
    $updatedOrgAgent = UpdateOrgAgent::make()->action(
        $orgAgent,
        [
            'status' => false
        ]
    );

    expect($updatedOrgAgent)->toBeInstanceOf(OrgAgent::class)
        ->and($updatedOrgAgent->status)->toBe(false);

    return $updatedOrgAgent;
})->depends('create org-agent');

test('create org-supplier', function ($supplier) {
    $orgSupplier = StoreOrgSupplier::make()->action(
        $this->organisation,
        $supplier
    );

    expect($orgSupplier)->toBeInstanceOf(OrgSupplier::class)
        ->and($orgSupplier->stats)->toBeInstanceOf(OrgSupplierStats::class);

    return $orgSupplier;
})->depends('create independent supplier');

test('update org-supplier', function ($orgSupplier) {
    $updatedOrgSupplier = UpdateOrgSupplier::make()->action(
        $orgSupplier,
        [
            'status' => false
        ]
    );

    expect($updatedOrgSupplier)->toBeInstanceOf(OrgSupplier::class)
        ->and($updatedOrgSupplier->status)->toBe(false);

    return $updatedOrgSupplier;
})->depends('create org-supplier');

test('delete agent', function () {
    $agent = Agent::first();

    $deletedAgent = DeleteAgent::make()->action($agent);

    expect(Agent::find($agent->id))->toBeNull();

    return $deletedAgent;
});

test('delete supplier', function () {
    $supplier = Supplier::first();

    $deletedSupplier = DeleteSupplier::make()->action($supplier);

    expect(Supplier::find($supplier->id))->toBeNull();

    return $deletedSupplier;
});
