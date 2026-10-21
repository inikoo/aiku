<?php

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Goods\Packaging\BuildEprPackingLines;
use App\Actions\Goods\Packaging\SyncPackingDropdownComponents;
use App\Actions\GoodsIn\StockDelivery\DispatchStockDelivery;
use App\Actions\GoodsIn\StockDelivery\ReceiveStockDelivery;
use App\Actions\GoodsIn\StockDeliveryPackingCheck\RecordStockDeliveryPackingCheck;
use App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrderStateToSubmitted;
use App\Actions\Procurement\PurchaseOrderTransaction\StorePurchaseOrderTransaction;
use App\Actions\Reports\GetEprImportCartons;
use App\Actions\Reports\GetEprInboundPacking;
use App\Actions\SupplyChain\Agent\StoreAgent;
use App\Actions\SupplyChain\SupplierProduct\StoreSupplierProduct;
use App\Actions\SupplyChain\PackingSpec\GetPurchaseOrderPackingSpecGaps;
use App\Actions\SupplyChain\PackingSpec\ReturnPackingSpec;
use App\Actions\SupplyChain\PackingSpec\StorePackingSpec;
use App\Actions\SupplyChain\PackingSpec\UpdateAgentPackingSpecCutOff;
use App\Actions\SupplyChain\PackingSpec\VerifyPackingSpec;
use App\Actions\Procurement\OrgAgent\StoreOrgAgent;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\SupplyChain\PackingSpec\PackingCheckResultEnum;
use App\Enums\SupplyChain\PackingSpec\PackingSpecStatusEnum;
use App\Models\Goods\EprPackingLine;
use App\Models\Goods\PackagingComponent;
use App\Models\GoodsIn\StockDelivery;
use App\Models\GoodsIn\StockDeliveryPackingCheck;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Models\SupplyChain\Agent;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SupplyChain\SupplierProductPackingSpec;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

require_once __DIR__.'/../Support/ProcurementHelpers.php';

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = group();
    $this->adminGuest   = createAdminGuest($this->group);
    $this->stocks       = createStocks($this->group);
    $this->orgStocks    = createOrgStocks($this->organisation, $this->stocks);

    $this->orgAgent = OrgAgent::orderBy('id')->first()
        ?? StoreOrgAgent::make()->action($this->organisation, StoreAgent::make()->action($this->group, Agent::factory()->definition()), []);

    SyncPackingDropdownComponents::run($this->group);
    $this->material = PackagingComponent::where('group_id', $this->group->id)->where('is_dropdown_item', true)->get()->keyBy('dropdown_key');

    $this->withoutVite();
});

/**
 * A received delivery of 200 units of a product packed 100 to a carton, 10 to a pack, with a spec of one master
 * carton (500 g), one bubble wrap per unit (2 g) and two silica gel sachets per carton.
 *
 * @return array{0: StockDelivery, 1: SupplierProductPackingSpec}
 */
function receivedDeliveryWithPackingSpec(object $test): array
{
    $stockDelivery   = createStockDeliveryWithItems($test, 'PSPEC-'.Str::upper(Str::random(6)), [200]);
    $supplierProduct = $stockDelivery->items()->first()->supplierProduct;

    $spec = storePackingSpec($test, $supplierProduct);

    $stockDelivery = DispatchStockDelivery::make()->action($stockDelivery);

    return [ReceiveStockDelivery::run($stockDelivery)->refresh(), $spec];
}

function storePackingSpec(object $test, SupplierProduct $supplierProduct, array $modelData = []): SupplierProductPackingSpec
{
    return StorePackingSpec::make()->handle($supplierProduct, array_merge([
        'packed_by' => 'agent',
        'lines'     => [
            ['packaging_component_id' => $test->material['master_carton']->id, 'level' => 'per_carton', 'quantity' => 1, 'weight_g_each' => 500],
            ['packaging_component_id' => $test->material['bubble_wrap']->id, 'level' => 'per_unit', 'quantity' => 1, 'weight_g_each' => 2],
            ['packaging_component_id' => $test->material['silica_gel']->id, 'level' => 'per_carton', 'quantity' => 2],
        ],
    ], $modelData), $test->adminGuest->getUser());
}

test('a new packing spec version supersedes the live one and weighs the packing per carton', function () {
    $stockDelivery   = createStockDeliveryWithItems($this, 'PSPEC-V'.Str::upper(Str::random(4)), [100]);
    $supplierProduct = $stockDelivery->items()->first()->supplierProduct;

    $first  = storePackingSpec($this, $supplierProduct);
    $second = storePackingSpec($this, $supplierProduct);

    expect($first->refresh()->status)->toBe(PackingSpecStatusEnum::SUPERSEDED)
        ->and($second->status)->toBe(PackingSpecStatusEnum::SUBMITTED)
        ->and($second->version)->toBe(2)
        ->and((float)$second->refresh()->packaging_weight_g)->toBe(700.0)
        ->and($supplierProduct->livePackingSpec->id)->toBe($second->id);
});

test('the first packing spec of a product needs an opened-carton photo', function () {
    $stockDelivery      = createStockDeliveryWithItems($this, 'PSPEC-P'.Str::upper(Str::random(4)), [100]);
    $orgSupplierProduct = OrgSupplierProduct::where('organisation_id', $this->organisation->id)
        ->where('supplier_product_id', $stockDelivery->items()->first()->supplier_product_id)
        ->firstOrFail();
    $route   = route('grp.models.org.org_supplier_product.packing_spec.store', ['organisation' => $this->organisation->id, 'orgSupplierProduct' => $orgSupplierProduct->id]);
    $payload = [
        'packed_by' => 'agent',
        'lines'     => [['packaging_component_id' => $this->material['master_carton']->id, 'level' => 'per_carton', 'quantity' => 1, 'weight_g_each' => 500]],
    ];

    $this->actingAs($this->adminGuest->getUser())->post($route, $payload)->assertSessionHasErrors('photo');

    $this->actingAs($this->adminGuest->getUser())
        ->post($route, [...$payload, 'photo' => UploadedFile::fake()->image('carton.jpg')])
        ->assertSessionHasNoErrors();

    $live = $orgSupplierProduct->supplierProduct->refresh()->livePackingSpec;
    expect(StorePackingSpec::make()->photoOf($live))->not->toBeNull();

    $this->actingAs($this->adminGuest->getUser())->post($route, $payload)->assertSessionHasNoErrors();

    $carriedOver = $orgSupplierProduct->supplierProduct->refresh()->livePackingSpec;
    expect($carriedOver->version)->toBe(2)
        ->and(StorePackingSpec::make()->photoOf($carriedOver)?->id)->toBe(StorePackingSpec::make()->photoOf($live)->id);
});

test('only an organisation that buys the product can verify or return its packing spec', function () {
    $stockDelivery    = createStockDeliveryWithItems($this, 'PSPEC-O'.Str::upper(Str::random(4)), [100]);
    $boughtProduct    = $stockDelivery->items()->first()->supplierProduct;
    $notBoughtProduct = StoreSupplierProduct::make()->action($boughtProduct->supplier, [
        'code'             => 'PSPEC-N'.Str::upper(Str::random(4)),
        'name'             => 'Not bought here',
        'cost'             => 100,
        'stock_id'         => $this->stocks[0]->id,
        'units_per_pack'   => 10,
        'units_per_carton' => 100,
    ]);
    $notBoughtIds = OrgSupplierProduct::where('supplier_product_id', $notBoughtProduct->id)->pluck('id');
    DB::table('org_supplier_product_stats')->whereIn('org_supplier_product_id', $notBoughtIds)->delete();
    DB::table('org_stock_has_org_supplier_products')->whereIn('org_supplier_product_id', $notBoughtIds)->delete();
    OrgSupplierProduct::whereIn('id', $notBoughtIds)->delete();
    $notBought = storePackingSpec($this, $notBoughtProduct);
    $bought    = storePackingSpec($this, $boughtProduct);
    $user      = $this->adminGuest->getUser();
    $route     = fn (string $action, SupplierProductPackingSpec $spec) => route("grp.models.org.packing_spec.$action", ['organisation' => $this->organisation->id, 'packingSpec' => $spec->id]);

    $this->actingAs($user)->post($route('verify', $notBought))->assertNotFound();
    $this->actingAs($user)->post($route('return', $notBought), ['reason' => 'Wrong'])->assertNotFound();
    $this->actingAs($user)->get($route('pdf', $notBought))->assertNotFound();
    expect($notBought->refresh()->status)->toBe(PackingSpecStatusEnum::SUBMITTED);

    $this->actingAs($user)->post($route('verify', $bought))->assertSessionHasNoErrors();
    expect($bought->refresh()->status)->toBe(PackingSpecStatusEnum::VERIFIED);
});

test('a returned packing spec is no longer verified', function () {
    $stockDelivery = createStockDeliveryWithItems($this, 'PSPEC-R'.Str::upper(Str::random(4)), [100]);
    $spec          = VerifyPackingSpec::make()->handle(storePackingSpec($this, $stockDelivery->items()->first()->supplierProduct), $this->adminGuest->getUser());

    $spec = ReturnPackingSpec::make()->handle($spec, 'Cartons are heavier', $this->adminGuest->getUser());

    expect($spec->refresh()->status)->toBe(PackingSpecStatusEnum::RETURNED)
        ->and($spec->verified_at)->toBeNull()
        ->and($spec->verified_by_user_id)->toBeNull();
});

test('receiving a delivery with a packing spec asks goods in to weigh it and builds the inbound packing lines', function () {
    [$stockDelivery, $spec] = receivedDeliveryWithPackingSpec($this);

    $check = StockDeliveryPackingCheck::where('stock_delivery_id', $stockDelivery->id)->sole();
    expect($check->packing_spec_id)->toBe($spec->id)
        ->and($check->weigh_materials)->toBeTrue()
        ->and($check->cartons_to_check)->toBe(1)
        ->and($check->staff_task_id)->not->toBeNull();

    $day = $stockDelivery->received_at->copy()->startOfDay();
    BuildEprPackingLines::run($this->organisation, $day, $day);

    $kg = EprPackingLine::where('stock_delivery_id', $stockDelivery->id)->get()->mapWithKeys(fn (EprPackingLine $line) => [$line->packaging_component_id => (float)$line->kg]);
    expect($kg->all())->toEqual([
        $this->material['master_carton']->id => 1.0,
        $this->material['bubble_wrap']->id   => 0.4,
    ]);

    $inbound = GetEprInboundPacking::run($this->organisation, $day, $day);
    $cartons = GetEprImportCartons::make()->handle($this->organisation, $day, $day);
    expect($inbound['coverage']['buckets']['declared']['cartons'])->toBeGreaterThanOrEqual(2.0)
        ->and($cartons)->toHaveKeys(['materials', 'cartons_with_spec', 'products_without_spec', 'kg']);
});

test('goods in weighs the packing: a mismatch returns the spec once, a zero weight is refused', function () {
    [$stockDelivery, $spec] = receivedDeliveryWithPackingSpec($this);
    $check = StockDeliveryPackingCheck::where('stock_delivery_id', $stockDelivery->id)->sole();
    $lines = $spec->lines()->get();
    $user  = $this->adminGuest->getUser();

    $this->actingAs($user)->post(route('grp.models.stock_delivery_packing_check.record', $check->id), [
        'carton_weights_g' => [10000],
        'lines'            => $lines->map(fn ($line) => ['id' => $line->id, 'weight_g_each' => 0])->all(),
    ])->assertSessionHasErrors('lines.0.weight_g_each');

    $weighed = ['carton_weights_g' => [10000], 'lines' => $lines->map(fn ($line) => ['id' => $line->id, 'weight_g_each' => 900])->all()];

    RecordStockDeliveryPackingCheck::make()->handle($check, $weighed, $user);
    $spec->refresh();
    $returnedAt = $spec->returned_at;

    expect($check->refresh()->result)->toBe(PackingCheckResultEnum::MISMATCH)
        ->and($spec->status)->toBe(PackingSpecStatusEnum::RETURNED);

    $this->travel(1)->minutes();
    RecordStockDeliveryPackingCheck::make()->handle($check->refresh(), $weighed, $user);

    expect($spec->refresh()->returned_at->equalTo($returnedAt))->toBeTrue();
});

test('from the cut-off date an agent order cannot be submitted while a product has no packing spec', function () {
    $orgAgent = createFreshOrgAgent($this);
    [$orgSupplier, $orgSupplierProduct] = createAgentOrgSupplierWithProduct($this, $orgAgent);

    $purchaseOrder = StorePurchaseOrder::make()->action($orgSupplier, PurchaseOrder::factory()->definition());
    StorePurchaseOrderTransaction::make()->action(
        $purchaseOrder,
        $orgSupplierProduct->supplierProduct->historicSupplierProduct,
        $this->orgStocks[0],
        array_merge(PurchaseOrderTransaction::factory()->definition(), ['quantity_ordered' => 100])
    );

    expect(GetPurchaseOrderPackingSpecGaps::run($purchaseOrder->refresh()))->toMatchArray(['blocking' => false, 'cut_off' => null])
        ->and(GetPurchaseOrderPackingSpecGaps::run((clone $purchaseOrder)->forceFill(['state' => PurchaseOrderStateEnum::SETTLED])))->toBeNull();

    UpdateAgentPackingSpecCutOff::make()->handle($orgAgent->agent, today()->toDateString());

    expect(fn () => UpdatePurchaseOrderStateToSubmitted::make()->action($purchaseOrder->refresh()))->toThrow(ValidationException::class);

    storePackingSpec($this, $orgSupplierProduct->supplierProduct);
    $purchaseOrder = UpdatePurchaseOrderStateToSubmitted::make()->action($purchaseOrder->refresh());

    expect($purchaseOrder->state->value)->toBe('submitted');
});
