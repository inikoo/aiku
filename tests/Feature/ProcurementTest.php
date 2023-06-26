<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:03:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Procurement\Agent\ChangeAgentOwner;
use App\Actions\Procurement\Agent\UpdateAgent;
use App\Actions\Procurement\Agent\UpdateAgentIsPrivate;
use App\Actions\Procurement\Marketplace\Agent\StoreMarketplaceAgent;
use App\Actions\Procurement\Marketplace\Supplier\StoreMarketplaceSupplier;
use App\Actions\Procurement\PurchaseOrder\AddItemPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\DeletePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrderItemQuantity;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToCheckedPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToConfirmPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToCreatingPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToDispatchedPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToManufacturedPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToReceivedPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToSettledPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdateStateToSubmittedPurchaseOrder;
use App\Actions\Procurement\Supplier\GetSupplier;
use App\Actions\Procurement\SupplierProduct\StoreSupplierProduct;
use App\Actions\Procurement\SupplierProduct\SyncSupplierProductTradeUnits;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\AttachAgent;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Procurement\Agent;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderItem;
use App\Models\Procurement\Supplier;
use App\Models\Procurement\SupplierProduct;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $tenant = Tenant::first();
    if (!$tenant) {
        $group = StoreGroup::make()->asAction(
            array_merge(
                Group::factory()->definition(),
                [
                    'code' => 'ACME'
                ]
            )
        );
        $tenant = StoreTenant::make()->action(
            $group,
            array_merge(
                Tenant::factory()->definition(),
                [
                    'code' => 'AGB'
                ]
            )
        );
        StoreTenant::make()->action(
            $group,
            array_merge(
                Tenant::factory()->definition(),
                [
                    'code' => 'AUS'
                ]
            )
        );
    }
    $tenant->makeCurrent();
});

test('create agent', function () {
    $modelData = Agent::factory()->definition();
    $agent     = StoreMarketplaceAgent::make()->action(
        tenant: app('currentTenant'),
        objectData: $modelData
    );

    expect($agent)->toBeInstanceOf(Agent::class)
        ->and(app('currentTenant')->procurementStats->number_agents)->toBe(1)
        ->and(app('currentTenant')->procurementStats->number_agents_status_owner)->toBe(1)
        ->and(app('currentTenant')->procurementStats->number_archived_agents)->toBe(0);

    $tenant2 = Tenant::where('slug', 'aus')->first();
    expect($tenant2->procurementStats->number_agents_status_owner)->toBe(0);

    return $agent;
});


test('create another agent', function () {
    $modelData = Agent::factory()->definition();
    $agent     = StoreMarketplaceAgent::make()->action(
        tenant: app('currentTenant'),
        objectData: $modelData
    );

    expect($agent)->toBeInstanceOf(Agent::class)
        ->and($agent->owner_type)->toBe('Tenant')
        ->and($agent->owner_id)->toBe(app('currentTenant')->id)
        ->and(app('currentTenant')->procurementStats->number_agents)->toBe(2)
        ->and(app('currentTenant')->procurementStats->number_agents_status_owner)->toBe(2)
        ->and(app('currentTenant')->procurementStats->number_archived_agents)->toBe(0);
    return $agent;
});


test('attach agent to other tenant', function ($agent) {
    expect($agent)->toBeInstanceOf(Agent::class);
    $tenant2 = Tenant::where('slug', 'aus')->first();
    AttachAgent::run(
        tenant:$tenant2,
        agent:$agent
    );

    expect($tenant2->procurementStats->number_agents)->toBe(1)
        ->and($tenant2->procurementStats->number_agents_status_owner)->toBe(0)
        ->and($tenant2->procurementStats->number_agents_status_adopted)->toBe(1);
})->depends('create another agent');


test('change agent visibility', function ($agent) {
    expect($agent->is_private)->toBeFalsy();
    $agent = UpdateAgentIsPrivate::run($agent, true);
    expect($agent->is_private)->toBeTrue();
    $agent = UpdateAgentIsPrivate::run($agent, false);
    expect($agent->is_private)->toBeFalse();
})->depends('create agent');

test('change agent owner', function ($agent) {
    $agent = ChangeAgentOwner::run($agent, app('currentTenant'));

    $this->assertModelExists($agent);
})->depends('create agent');

test('check if last tenant cant update', function ($agent) {
    $tenant2 = Tenant::where('slug', 'aus')->first();
    $tenant2->makeCurrent();

    expect(function () use ($agent) {
        UpdateAgent::make()->action($agent, Agent::factory()->definition());
    })->toThrow(ValidationException::class);
})->depends('create agent');

test('create independent supplier', function () {
    $supplier = StoreMarketplaceSupplier::make()->action(
        owner: app('currentTenant'),
        agent: null,
        modelData: Supplier::factory()->definition()
    );
    expect($supplier)->toBeInstanceOf(Supplier::class)
        ->and($supplier->agent_id)->toBeNull()
        ->and(app('currentTenant')->procurementStats->number_suppliers)->toBe(1);


    return $supplier;
});

test('create independent supplier 2', function () {
    $supplier = StoreMarketplaceSupplier::make()->action(
        owner: app('currentTenant'),
        agent: null,
        modelData: Supplier::factory()->definition(),
    );
    expect($supplier)->toBeInstanceOf(Supplier::class)
        ->and($supplier->agent_id)->toBeNull()
        ->and(app('currentTenant')->procurementStats->number_suppliers)->toBe(2);


    return $supplier;
});

test('number independent supplier should be two', function () {
    $this->assertEquals(2, app('currentTenant')->procurementStats->number_suppliers);
});

test('create supplier in agent', function ($agent) {
    expect($agent->stats->number_suppliers)->toBe(0);

    $supplier = StoreMarketplaceSupplier::make()->action(
        owner: app('currentTenant'),
        agent: $agent,
        modelData: Supplier::factory()->definition()
    );
    $agent->refresh();
    expect($supplier)->toBeInstanceOf(Supplier::class)
        ->and($agent->stats->number_suppliers)->toBe(1);

    return $supplier;
})->depends('create agent');

test('create supplier product independent supplier', function ($supplier) {
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());
    $this->assertModelExists($supplierProduct);

    return $supplierProduct;
})->depends('create independent supplier');

test('create supplier product independent supplier 2', function ($supplier) {
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());
    $this->assertModelExists($supplierProduct);

    return $supplierProduct;
})->depends('create independent supplier');

test('create supplier product in agent supplier', function ($supplier) {
    $supplierProduct = StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());
    expect($supplierProduct)->toBeInstanceOf(SupplierProduct::class);
})->depends('create supplier in agent');

test('others tenant can view supplier', function ($agent) {
    $tenant = Tenant::where('slug', 'aus')->first();
    $tenant->makeCurrent();

    $supplier = GetSupplier::run($agent);

    expect($supplier)->toBeInstanceOf(LengthAwarePaginator::class);
})->depends('create agent');

test('create trade unit', function () {
    $tradeUnit = StoreTradeUnit::make()->action(TradeUnit::factory()->definition());
    $this->assertModelExists($tradeUnit);

    return $tradeUnit;
});

test('create purchase order independent supplier', function ($supplier) {
    $purchaseOrder = StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition());

    expect($purchaseOrder)->toBeInstanceOf(PurchaseOrder::class)
        ->and($supplier->stats->number_purchase_orders)->toBe(1);

    return $purchaseOrder;
})->depends('create independent supplier');

test('add items to purchase order', function ($purchaseOrder) {
    $i = 1;
    do {
        AddItemPurchaseOrder::make()->action($purchaseOrder, PurchaseOrderItem::factory()->definition());
        $i++;
    } while ($i <= 5);

    $purchaseOrder->load('items');
    expect($purchaseOrder->items()->count())->toBe(5);

    return $purchaseOrder;
})->depends('create purchase order independent supplier');


test('delete purchase order', function () {
    $supplier = StoreMarketplaceSupplier::make()->action(
        owner: app('currentTenant'),
        agent: null,
        modelData: Supplier::factory()->definition()
    );
    StoreSupplierProduct::make()->action($supplier, SupplierProduct::factory()->definition());

    $purchaseOrder = StorePurchaseOrder::make()->action($supplier, PurchaseOrder::factory()->definition());


    expect($supplier->stats->number_purchase_orders)->toBe(1);
    $purchaseOrderDeleted = false;
    try {
        $purchaseOrderDeleted = DeletePurchaseOrder::make()->action($purchaseOrder->fresh());
    } catch (ValidationException) {
    }
    $supplier->refresh();

    expect($purchaseOrderDeleted)->toBeTrue()->and($supplier->stats->number_purchase_orders)->toBe(0);
});

test('update quantity items to 0 in purchase order', function ($purchaseOrder) {
    $item = $purchaseOrder->items()->first();

    $item = UpdatePurchaseOrderItemQuantity::make()->action($item, [
        'unit_quantity' => 0
    ]);

    $this->assertModelMissing($item);
    expect($purchaseOrder->items()->count())->toBe(4);
})->depends('add items to purchase order');

test('update quantity items in purchase order', function ($purchaseOrder) {
    $item = $purchaseOrder->items()->first();

    $item = UpdatePurchaseOrderItemQuantity::make()->action($item, [
        'unit_quantity' => 12
    ]);
    expect($item)->toBeInstanceOf(PurchaseOrderItem::class)->and($item->unit_quantity)->toBe(12);
})->depends('add items to purchase order');

test('sync supplier product and trade units', function ($supplier) {
    $syncSupplierProductTradeUnit = SyncSupplierProductTradeUnits::run($supplier, [1]);
    $this->assertModelExists($syncSupplierProductTradeUnit);
})->depends('create supplier product independent supplier');

test('update purchase order', function ($agent) {
    $purchaseOrder = UpdatePurchaseOrder::make()->action($agent, PurchaseOrder::factory()->definition());
    $this->assertModelExists($purchaseOrder);
})->depends('create purchase order independent supplier');

test('create purchase order by agent', function ($agent) {
    $purchaseOrder = StorePurchaseOrder::make()->action($agent, PurchaseOrder::factory()->definition());
    $this->assertModelExists($purchaseOrder);
})->depends('create agent');

test('change state to submit purchase order', function ($purchaseOrder) {
    $purchaseOrder->refresh();
    try {
        $purchaseOrder = UpdateStateToSubmittedPurchaseOrder::make()->action($purchaseOrder);
    } catch (ValidationException) {
    }
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::SUBMITTED);

    return $purchaseOrder;
})->depends('add items to purchase order');

test('change state to confirm purchase order', function ($purchaseOrder) {
    try {
        $purchaseOrder = UpdateStateToConfirmPurchaseOrder::make()->action($purchaseOrder);
    } catch (ValidationException) {
    }
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CONFIRMED);

    return $purchaseOrder;
})->depends('change state to submit purchase order');

test('change state to manufactured purchase order', function ($purchaseOrder) {
    try {
        $purchaseOrder = UpdateStateToManufacturedPurchaseOrder::make()->action($purchaseOrder);
    } catch (ValidationException) {
    }
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::MANUFACTURED);

    return $purchaseOrder;
})->depends('create purchase order independent supplier');

test('change state to dispatched from manufacture purchase order', function ($purchaseOrder) {
    try {
        $purchaseOrder = UpdateStateToDispatchedPurchaseOrder::make()->action($purchaseOrder);
    } catch (ValidationException) {
    }
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::DISPATCHED);

    return $purchaseOrder;
})->depends('change state to confirm purchase order');

test('change state to received from dispatch purchase order', function ($purchaseOrder) {
    try {
        $purchaseOrder = UpdateStateToReceivedPurchaseOrder::make()->action($purchaseOrder);
    } catch (ValidationException) {
    }
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::RECEIVED);

    return $purchaseOrder;
})->depends('change state to manufactured purchase order');

test('change state to checked from received purchase order', function ($purchaseOrder) {
    try {
        $purchaseOrder = UpdateStateToCheckedPurchaseOrder::make()->action($purchaseOrder);
    } catch (ValidationException) {
    }
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CHECKED);

    return $purchaseOrder;
})->depends('change state to received from dispatch purchase order');

test('change state to settled from checked purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToSettledPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::SETTLED);

    return $purchaseOrder;
})->depends('change state to checked from received purchase order');

test('change state to checked from settled purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToCheckedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CHECKED);
})->depends('create purchase order independent supplier');

test('change state to received from checked purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToReceivedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::RECEIVED);
})->depends('create purchase order independent supplier');

test('change state to dispatched from received purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToDispatchedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::DISPATCHED);
})->depends('create purchase order independent supplier');

test('change state to manufactured from dispatched purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToManufacturedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::MANUFACTURED);
})->depends('create purchase order independent supplier');

test('change state to confirmed from manufactured purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToConfirmPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CONFIRMED);
})->depends('create purchase order independent supplier');

test('change state to submitted from confirmed purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToSubmittedPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::SUBMITTED);
})->depends('create purchase order independent supplier');

test('change state to creating from submitted purchase order', function ($purchaseOrder) {
    $purchaseOrder = UpdateStateToCreatingPurchaseOrder::make()->action($purchaseOrder);
    expect($purchaseOrder->state)->toEqual(PurchaseOrderStateEnum::CREATING);
})->depends('create purchase order independent supplier');
