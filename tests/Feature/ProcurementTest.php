<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 15:51:21 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Procurement\Agent\ChangeAgentOwner;
use App\Actions\Procurement\Agent\StoreAgent;
use App\Actions\Procurement\Agent\UpdateAgent;
use App\Actions\Procurement\Agent\UpdateAgentVisibility;
use App\Actions\Procurement\PurchaseOrder\AddItemPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\DeletePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\SubmitPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UnSubmitPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrderItemQuantity;
use App\Actions\Procurement\Supplier\GetSupplier;
use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Actions\Procurement\SupplierProduct\StoreSupplierProduct;
use App\Models\Helpers\Address;
use App\Models\Procurement\Agent;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use App\Models\Tenancy\Tenant;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

beforeAll(fn() => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $this->tenant = Tenant::where('slug', 'agb')->first();
    $this->tenant2 = Tenant::where('slug', 'aus')->first();
    $this->tenant3 = Tenant::where('slug', 'aes')->first();

    $this->tenant->makeCurrent();
});

test('create agent', function () {
    $agent = StoreAgent::make()->action($this->tenant, Agent::factory()->definition(), Address::factory()->definition());
    $this->assertModelExists($agent);
    return $agent;
});

test('number of agents should be one', function () {
    $this->assertEquals(1, $this->tenant->procurementStats->number_agents);
    $this->assertEquals(1, $this->tenant->procurementStats->number_active_agents);
})->depends('create agent');

test('create another agent', function () {
    $agent = StoreAgent::make()->action($this->tenant, Agent::factory()->definition(), Address::factory()->definition());
    $this->assertModelExists($agent);
});

test('number of agents should be two', function () {
    $this->assertEquals(2, $this->tenant->procurementStats->number_agents);
    $this->assertEquals(2, $this->tenant->procurementStats->number_active_agents);
})->depends('create agent', 'create another agent');

test('create independent supplier', function () {
    $supplier = StoreSupplier::make()->action($this->tenant, Arr::prepend(Supplier::factory()->definition(), 'supplier', 'type'));
    $this->assertModelExists($supplier);

    return $supplier;
});

test('number independent supplier should be one', function () {
    $this->assertEquals(1, $this->tenant->procurementStats->number_suppliers);
    $this->assertEquals(1, $this->tenant->procurementStats->number_active_suppliers);
});

test('create independent supplier 2', function () {
    $supplier = StoreSupplier::make()->action($this->tenant, Arr::prepend(Supplier::factory()->definition(), 'supplier', 'type'));
    $this->assertModelExists($supplier);

    return $supplier;
});

test('number independent supplier should be two', function () {
    $this->assertEquals(2, $this->tenant->procurementStats->number_suppliers);
    $this->assertEquals(2, $this->tenant->procurementStats->number_active_suppliers);
});

test('create supplier in agent', function ($agent) {
    $supplier = StoreSupplier::make()->action($agent, Arr::prepend(Supplier::factory()->definition(), 'sub-supplier', 'type'));
    $this->assertModelExists($supplier);
})->depends('create agent');

test('create supplier product', function ($supplier) {
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());
    $this->assertModelExists($supplierProduct);
})->depends('create independent supplier');

test('create purchase order', function ($supplier) {
    $purchaseOrder = StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition());
    $this->assertModelExists($purchaseOrder);

    return $purchaseOrder;
})->depends('create independent supplier');

test('check has one purchase order', function ($supplier) {
    $this->assertEquals(1, $supplier->stats->number_products);
    $this->assertEquals(1, $supplier->stats->number_purchase_orders);
})->depends('create independent supplier');

test('create supplier product 2', function ($supplier) {
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());
    $this->assertModelExists($supplierProduct);
})->depends('create independent supplier');

test('create new purchase order', function ($supplier) {
    expect(function () use ($supplier) {
        StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition());
    })->toThrow(ValidationException::class);
})->depends('create independent supplier');

test('create new purchase order by force', function ($supplier) {
    $purchaseOrder = StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition(), true);
    $this->assertModelExists($purchaseOrder);

    return $purchaseOrder;
})->depends('create independent supplier');

test('check if agent match with tenant', function ($agent) {
    $agent = $agent->where('owner_id', $this->tenant->id)->first();

    $this->assertModelExists($agent);
})->depends('create agent');

test('delete purchase order when items 0', function ($purchaseOrder) {
    expect(function () use ($purchaseOrder) {
        DeletePurchaseOrder::make()->action($purchaseOrder->first());
    })->toThrow(ValidationException::class);
})->depends('create new purchase order by force');

test('add items to purchase order', function ($purchaseOrder) {
    $items = AddItemPurchaseOrder::make()->action($purchaseOrder, PurchaseOrderItem::factory()->definition());
    $this->assertModelExists($items);

    return $items->first();
})->depends('create new purchase order by force');

test('add more than 1 items to purchase order', function ($purchaseOrder) {
    $i = 0;
    do {
        $items = AddItemPurchaseOrder::make()->action($purchaseOrder, PurchaseOrderItem::factory()->definition());
        $i++;
    } while ($i < 5);

    $this->assertModelExists($items);

    return $items->first();
})->depends('create new purchase order by force');

test('delete purchase order', function ($purchaseOrder) {
    $purchaseOrder = DeletePurchaseOrder::make()->action($purchaseOrder->fresh());

    $this->assertSoftDeleted($purchaseOrder);
})->depends('create new purchase order by force');

test('update quantity items to 0 in purchase order', function ($item) {
    $item = UpdatePurchaseOrderItemQuantity::make()->action($item, [
        'unit_quantity' => 0
    ]);

    $this->assertModelMissing($item);
})->depends('add items to purchase order');

test('update quantity items in purchase order', function ($item) {
    $item = UpdatePurchaseOrderItemQuantity::make()->action($item, [
        'unit_quantity' => 12
    ]);

    $this->assertModelMissing($item);
})->depends('add items to purchase order');

test('create purchase order by agent', function ($agent) {
    $purchaseOrder = StorePurchaseOrder::make()->action($agent, PurchaseOrder::factory()->definition());
    $this->assertModelExists($purchaseOrder);
})->depends('create agent');

test('submit purchase order', function ($purchaseOrder) {
    $purchaseOrder = SubmitPurchaseOrder::make()->action($purchaseOrder);
    $this->assertModelExists($purchaseOrder);
})->depends('create new purchase order by force');

test('un submit purchase order', function ($purchaseOrder) {
    $purchaseOrder = UnSubmitPurchaseOrder::make()->action($purchaseOrder);
    $this->assertModelExists($purchaseOrder);
})->depends('create new purchase order by force');

test('check if agent not match with tenant', function ($agent) {
    $agent = $agent->where('owner_id', $this->tenant2->id)->first();

    expect($agent)->toBeNull();
})->depends('create agent');

test('others tenant can view supplier', function ($agent) {
    $this->tenant2->makeCurrent();
    $supplier = GetSupplier::run($agent);

    expect($supplier)->toBeInstanceOf(LengthAwarePaginator::class);
})->depends('create agent');

test('cant change agent visibility to private', function ($agent) {
    expect(function () use ($agent) {
        UpdateAgentVisibility::make()->action($agent, false);
    })->toThrow(ValidationException::class);
})->depends('create agent');

test('change agent visibility to public', function ($agent) {
    $agent = UpdateAgentVisibility::make()->action($agent->first(), false);

    $this->assertModelExists($agent);
})->depends('create agent');

test('change agent owner', function ($agent) {
    $agent = ChangeAgentOwner::run($agent, $this->tenant2);

    $this->assertModelExists($agent);
})->depends('create agent');

test('check if last tenant cant update', function ($agent) {
    expect(function () use ($agent) {
        UpdateAgent::make()->action($agent, Agent::factory()->definition());
    })->toThrow(ValidationException::class);
})->depends('create agent');
