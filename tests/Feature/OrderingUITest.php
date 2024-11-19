<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 30-10-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

use App\Actions\Ordering\Order\StoreOrder;
use App\Actions\Ordering\Purge\StorePurge;
use App\Actions\Ordering\ShippingZoneSchema\StoreShippingZoneSchema;
use App\Actions\Ordering\Transaction\StoreTransaction;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Purge\PurgeTypeEnum;
use App\Models\Catalogue\HistoricAsset;
use App\Models\Helpers\Address;
use App\Models\Ordering\Order;
use App\Models\Ordering\Purge;
use App\Models\Ordering\ShippingZoneSchema;
use App\Models\Ordering\Transaction;
use Inertia\Testing\AssertableInertia;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses()->group('ui');

beforeAll(function () {
    loadDB();
});


beforeEach(function () {
    list(
        $this->organisation,
        $this->user,
        $this->shop
    ) = createShop();

    list(
        $this->tradeUnit,
        $this->product
    ) = createProduct($this->shop);

    $this->customer = createCustomer($this->shop);

    $shippingZoneSchema = ShippingZoneSchema::first();
    if (!$shippingZoneSchema) {
        $shippingZoneSchema = StoreShippingZoneSchema::make()->action($this->shop, ShippingZoneSchema::factory()->definition());
    }
    $this -> shippingZoneSchema = $shippingZoneSchema;

    $purge = Purge::where('shop_id', $this->shop->id)->first();
    // dd($purge);
    if (!$purge) {
        $billingAddress  = new Address(Address::factory()->definition());
        $deliveryAddress = new Address(Address::factory()->definition());

        $modelData = Order::factory()->definition();
        data_set($modelData, 'billing_address', $billingAddress);
        data_set($modelData, 'delivery_address', $deliveryAddress);

        $order = StoreOrder::make()->action(parent:$this->customer, modelData:$modelData);


        $transactionData = Transaction::factory()->definition();
        $historicAsset   = $this->product->historicAsset;
        // expect($historicAsset)->toBeInstanceOf(HistoricAsset::class);
        $transaction = StoreTransaction::make()->action($order, $historicAsset, $transactionData);

        $order->refresh();

        // expect($order)->toBeInstanceOf(Order::class)
        // ->and($order->state)->toBe(OrderStateEnum::CREATING)
        //     ->and($order->stats->number_transactions)->toBe(1)
        //     ->and($order->stats->number_transactions_at_creation)->toBe(1);
        // expect($transaction)->toBeInstanceOf(Transaction::class);

        $this->customer->refresh();
        $shop = $order->shop;
        $shop->refresh();
        $order->update([
            'updated_at' => Date::now()->subDays(40)->toDateString()
        ]);

        $purge = StorePurge::make()->action($this->shop, [
            'type' => PurgeTypeEnum::MANUAL,
            'scheduled_at' => now(),
            'inactive_days' => 30
        ],);
    }
    $this->purge = $purge;
    // dd($purge);

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->user);
});


test('UI index asset shipping', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.shops.show.billables.shipping.index', [$this->organisation->slug, $this->shop]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/Shippings')
            ->where('title', 'shipping')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Shipping')
                        ->etc()
            )
            ->has('data');
    });
});

test('UI create asset shipping', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.shops.show.billables.shipping.create', [$this->organisation->slug, $this->shop]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->where('title', 'new schema')
            ->has('breadcrumbs', 4)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'new schema')
                        ->etc()
            )
            ->has('formData');
    });
});

test('UI show asset shipping', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.shops.show.billables.shipping.show', [$this->organisation->slug, $this->shop, $this->shippingZoneSchema]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Catalogue/ShippingZoneSchema')
            ->where('title', 'Shipping Zone Schema')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->shippingZoneSchema->name)
                        ->etc()
            )
            ->has('navigation')
            ->has('tabs');
    });
});

test('UI edit asset shipping', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.shops.show.billables.shipping.edit', [$this->organisation->slug, $this->shop, $this->shippingZoneSchema]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->where('title', 'Shipping Zone Schema')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->shippingZoneSchema->name)
                        ->etc()
            )
            ->has('navigation')
            ->has('formData');
    });
});

test('UI show ordering backlog', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.shops.show.ordering.backlog', [$this->organisation->slug, $this->shop]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Ordering/OrdersBacklog')
            ->where('title', 'orders backlog')
            ->has('breadcrumbs', 3)
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'orders backlog')
                        ->etc()
            );
    });
});

test('UI index ordering purges', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.shops.show.ordering.purges.index', [$this->organisation->slug, $this->shop]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('Org/Ordering/Purges')
            ->where('title', 'purges')
            ->has('breadcrumbs', 3)
            ->has('data')
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'Purges')
                        ->etc()
            );
    });
});

test('UI create ordering purge', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.shops.show.ordering.purges.create', [$this->organisation->slug, $this->shop]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('CreateModel')
            ->where('title', 'new purge')
            ->has('breadcrumbs', 4)
            ->has('formData', fn ($page) => $page
                ->where('route', [
                    'name'       => 'grp.models.purge.store',
                    'parameters' => [
                        'shop'         => $this->shop->id,
                    ]
                ])
                ->etc())
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', 'new purge')
                        ->etc()
            );
    });
});

test('UI edit ordering purge', function () {
    $this->withoutExceptionHandling();
    $response = get(route('grp.org.shops.show.ordering.purges.edit', [$this->organisation->slug, $this->shop, $this->purge]));
    $response->assertInertia(function (AssertableInertia $page) {
        $page
            ->component('EditModel')
            ->where('title', 'Purge')
            ->has('breadcrumbs', 3)
            ->has('formData', fn ($page) => $page
                ->where('args', [
                    'updateRoute' => [
                        'name'       => 'grp.models.purge.update',
                        'parameters' => $this->purge->id

                    ],
                ])
                ->etc())
            ->has(
                'pageHead',
                fn (AssertableInertia $page) => $page
                        ->where('title', $this->purge->scheduled_at->toISOString())
                        ->etc()
            );
    });
});
