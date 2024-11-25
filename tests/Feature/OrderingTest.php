<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:03:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Accounting\Invoice\StoreInvoice;
use App\Actions\Accounting\Invoice\UpdateInvoice;
use App\Actions\Accounting\InvoiceTransaction\StoreInvoiceTransaction;
use App\Actions\Accounting\InvoiceTransaction\UpdateInvoiceTransaction;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\Dropshipping\CustomerClient\StoreCustomerClient;
use App\Actions\Dropshipping\CustomerClient\UpdateCustomerClient;
use App\Actions\Ordering\Order\DeleteOrder;
use App\Actions\Ordering\Order\SendOrderToWarehouse;
use App\Actions\Ordering\Order\StoreOrder;
use App\Actions\Ordering\Order\UpdateOrder;
use App\Actions\Ordering\Order\UpdateOrderStateToSubmitted;
use App\Actions\Ordering\Order\UpdateStateToCreatingOrder;
use App\Actions\Ordering\Order\UpdateStateToFinalizedOrder;
use App\Actions\Ordering\Order\UpdateStateToHandlingOrder;
use App\Actions\Ordering\Order\UpdateStateToPackedOrder;
use App\Actions\Ordering\Purge\StorePurge;
use App\Actions\Ordering\Purge\UpdatePurge;
use App\Actions\Ordering\PurgedOrder\UpdatePurgedOrder;
use App\Actions\Ordering\ShippingZone\StoreShippingZone;
use App\Actions\Ordering\ShippingZone\UpdateShippingZone;
use App\Actions\Ordering\ShippingZoneSchema\StoreShippingZoneSchema;
use App\Actions\Ordering\ShippingZoneSchema\UpdateShippingZoneSchema;
use App\Actions\Ordering\Transaction\DeleteTransaction;
use App\Actions\Ordering\Transaction\StoreTransaction;
use App\Actions\Ordering\Transaction\UpdateTransaction;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Purge\PurgeTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\Catalogue\HistoricAsset;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Helpers\Address;
use App\Models\Ordering\Order;
use App\Models\Ordering\Purge;
use App\Models\Ordering\PurgedOrder;
use App\Models\Ordering\ShippingZone;
use App\Models\Ordering\ShippingZoneSchema;
use App\Models\Ordering\Transaction;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Date;

beforeAll(function () {
    loadDB();
});

beforeEach(function () {
    list(
        $this->organisation,
        $this->user,
        $this->shop
    ) = createShop();

    $this->group = $this->organisation->group;

    list(
        $this->tradeUnit,
        $this->product
    ) = createProduct($this->shop);

    $this->customer = createCustomer($this->shop);

    createWarehouse();
});


test('create shipping zone schema', function () {
    $shippingZoneSchema = StoreShippingZoneSchema::make()->action($this->shop, ShippingZoneSchema::factory()->definition());
    expect($shippingZoneSchema)->toBeInstanceOf(ShippingZoneSchema::class);

    return $shippingZoneSchema;
});

test('update shipping zone schema', function ($shippingZoneSchema) {
    $shippingZoneSchema = UpdateShippingZoneSchema::make()->action($shippingZoneSchema, ShippingZoneSchema::factory()->definition());
    $this->assertModelExists($shippingZoneSchema);
})->depends('create shipping zone schema');

test('create shipping zone', function ($shippingZoneSchema) {
    $shippingZone = StoreShippingZone::make()->action($shippingZoneSchema, ShippingZone::factory()->definition());
    $this->assertModelExists($shippingZoneSchema);

    return $shippingZone;
})->depends('create shipping zone schema');

test('update shipping zone', function ($shippingZone) {
    $shippingZone = UpdateShippingZone::make()->action($shippingZone, ShippingZone::factory()->definition());
    $this->assertModelExists($shippingZone);
})->depends('create shipping zone');


test('create order', function () {
    $billingAddress  = new Address(Address::factory()->definition());
    $deliveryAddress = new Address(Address::factory()->definition());

    $modelData = Order::factory()->definition();
    data_set($modelData, 'billing_address', $billingAddress);
    data_set($modelData, 'delivery_address', $deliveryAddress);


    $order = StoreOrder::make()->action($this->customer, $modelData);
    $this->customer->refresh();

    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->state)->toBe(OrderStateEnum::CREATING)
        ->and($order->customer)->toBeInstanceOf(Customer::class)
        ->and($this->group->orderingStats->number_orders)->toBe(1)
        ->and($this->group->orderingStats->number_orders_state_creating)->toBe(1)
        ->and($this->group->orderingStats->number_orders_handing_type_shipping)->toBe(1)
        ->and($this->organisation->salesStats->number_orders)->toBe(1)
        ->and($this->organisation->salesStats->number_orders_state_creating)->toBe(1)
        ->and($this->organisation->salesStats->number_orders_handing_type_shipping)->toBe(1)
        ->and($this->shop->orderingStats->number_orders)->toBe(1)
        ->and($this->shop->orderingStats->number_orders_state_creating)->toBe(1)
        ->and($this->shop->orderingStats->number_orders_handing_type_shipping)->toBe(1)
        ->and($this->customer->stats->number_orders)->toBe(1)
        ->and($this->customer->stats->number_orders_state_creating)->toBe(1)
        ->and($this->customer->stats->number_orders_handing_type_shipping)->toBe(1)
        ->and($order->stats->number_transactions_at_creation)->toBe(0);

    return $order;
});


test('create transaction', function ($order) {
    $transactionData = Transaction::factory()->definition();
    $historicAsset   = $this->product->historicAsset;
    expect($historicAsset)->toBeInstanceOf(HistoricAsset::class);
    $transaction = StoreTransaction::make()->action($order, $historicAsset, $transactionData);

    $order->refresh();

    expect($transaction)->toBeInstanceOf(Transaction::class)
        ->and($transaction->order->stats->number_transactions_at_creation)->toBe(1)
        ->and($order->stats->number_transactions)->toBe(1);

    return $transaction;
})->depends('create order');

test('update transaction', function ($transaction) {
    $order = UpdateTransaction::make()->action($transaction, Transaction::factory()->definition());

    $this->assertModelExists($order);
})->depends('create transaction');


test('update order', function ($order) {
    $order = UpdateOrder::make()->action($order, Order::factory()->definition());

    $this->assertModelExists($order);
})->depends('create order');

test('update order state to submitted', function (Order $order) {
    $order = UpdateOrderStateToSubmitted::make()->action($order);
    expect($order->state)->toEqual(OrderStateEnum::SUBMITTED)
        ->and($order->shop->orderingStats->number_orders_state_submitted)->toBe(1)
        ->and($order->organisation->salesStats->number_orders_state_submitted)->toBe(1)
        ->and($order->group->salesStats->number_orders_state_submitted)->toBe(1)
        ->and($order->stats->number_transactions)->toBe(1);

    return $order;
})->depends('create order');


test('update order state to in warehouse', function (Order $order) {
    $deliveryNote = SendOrderToWarehouse::make()->action($order, []);
    $order->refresh();
    expect($deliveryNote)->toBeInstanceOf(DeliveryNote::class)
        ->and($order->state)->toEqual(OrderStateEnum::IN_WAREHOUSE);
})->depends('update order state to submitted');

test('update state to packed from handling', function ($order) {
    try {
        $order = UpdateStateToPackedOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toBe(OrderStateEnum::PACKED);
})->depends('create order')->todo();

test('update state to finalized from handling', function ($order) {
    try {
        $order = UpdateStateToFinalizedOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toBe(OrderStateEnum::FINALISED);
})->depends('create order')->todo();


test('update state to finalized from settled', function ($order) {
    try {
        $order = UpdateStateToFinalizedOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toBe(OrderStateEnum::FINALISED);
})->depends('create order')->todo();

test('update state to packed from finalized', function ($order) {
    try {
        $order = UpdateStateToPackedOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toBe(OrderStateEnum::PACKED);
})->depends('create order')->todo();

test('update state to handling from packed', function ($order) {
    try {
        $order = UpdateStateToHandlingOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toEqual(OrderStateEnum::HANDLING);
})->depends('create order')->todo();

test('update state to submit from handling', function ($order) {
    try {
        $order = UpdateOrderStateToSubmitted::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toEqual(OrderStateEnum::SUBMITTED);
})->depends('create order')->todo();

test('update state to creating from submitted', function ($order) {
    try {
        $order = UpdateStateToCreatingOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toEqual(OrderStateEnum::CREATING);
})->depends('create order')->todo();

test('delete order', function ($order) {
    $order = DeleteOrder::run($order);

    $this->assertSoftDeleted($order);
})->depends('create order')->todo();

test('create customer client', function () {
    $shop           = StoreShop::make()->action($this->organisation, Shop::factory()->definition());
    $customer       = StoreCustomer::make()->action($shop, Customer::factory()->definition());
    $customerClient = StoreCustomerClient::make()->action($customer, CustomerClient::factory()->definition());
    $this->assertModelExists($customerClient);
    expect($customerClient->shop->code)->toBe($shop->code)
        ->and($customerClient->customer->reference)->toBe($customer->reference);

    return $customerClient;
});

test('update customer client', function ($customerClient) {
    $customerClient = UpdateCustomerClient::make()->action($customerClient, ['reference' => '001']);
    expect($customerClient->reference)->toBe('001');
})->depends('create customer client');

test('create invoice from customer', function () {
    $invoiceData = Invoice::factory()->definition();
    data_set($invoiceData, 'billing_address', new Address(Address::factory()->definition()));
    $invoice = StoreInvoice::make()->action($this->customer, $invoiceData);
    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoice->customer)->toBeInstanceOf(Customer::class)
        ->and($invoice->reference)->toBe('00001')
        ->and($invoice->customer->stats->number_invoices)->toBe(1);

    return $invoice;
})->depends();

test('update invoice from customer', function ($invoice) {
    $invoice = UpdateInvoice::make()->action($invoice, [
        'reference' => '00001a'

    ]);
    expect($invoice->reference)->toBe('00001a');
})->depends('create invoice from customer');

test('create invoice from order', function (Order $order) {
    $transaction = $order->transactions->first();
    $invoiceData = Invoice::factory()->definition();
    data_set($invoiceData, 'billing_address', new Address(Address::factory()->definition()));
    data_set($invoiceData, 'reference', '00002');
    $invoice  = StoreInvoice::make()->action($order, $invoiceData);
    $invoiceTransaction = StoreInvoiceTransaction::make()->action($invoice, $transaction, [
        'date' => now(),
        'tax_category_id' => $transaction->tax_category_id,
        'quantity' => 10,
        'gross_amount' => 1000,
        'net_amount' => 1000,
    ]);
    $customer = $invoice->customer;
    $this->shop->refresh();
    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($customer)->toBeInstanceOf(Customer::class)
        ->and($invoice->customer->id)->toBe($order->customer_id)
        ->and($invoice->reference)->toBe('00002')
        ->and($customer->stats->number_invoices)->toBe(2)
        ->and($this->shop->orderingStats->number_invoices)->toBe(2);
    expect($invoiceTransaction)->toBeInstanceOf(InvoiceTransaction::class);


    return $invoice;
})->depends('create order', 'update invoice from customer');

test('update invoice transaction', function (Invoice $invoice) {
    $transaction = $invoice->invoiceTransactions->first();
    $updatedTransaction = UpdateInvoiceTransaction::make()->action($transaction, [
        'quantity' => 100
    ]);
    expect($updatedTransaction)->toBeInstanceOf(InvoiceTransaction::class)
        ->and(intval($updatedTransaction->quantity))->toBe(100);

    return $updatedTransaction;
})->depends('create invoice from order');

test('create old order', function () {
    $billingAddress  = new Address(Address::factory()->definition());
    $deliveryAddress = new Address(Address::factory()->definition());

    $modelData = Order::factory()->definition();
    data_set($modelData, 'billing_address', $billingAddress);
    data_set($modelData, 'delivery_address', $deliveryAddress);

    $order = StoreOrder::make()->action(parent:$this->customer, modelData:$modelData);


    $transactionData = Transaction::factory()->definition();
    $historicAsset   = $this->product->historicAsset;
    expect($historicAsset)->toBeInstanceOf(HistoricAsset::class);
    $transaction = StoreTransaction::make()->action($order, $historicAsset, $transactionData);

    $order->refresh();

    expect($order)->toBeInstanceOf(Order::class)
    ->and($order->state)->toBe(OrderStateEnum::CREATING)
        ->and($order->stats->number_transactions)->toBe(1)
        ->and($order->stats->number_transactions_at_creation)->toBe(1);
    expect($transaction)->toBeInstanceOf(Transaction::class);

    $this->customer->refresh();
    $shop = $order->shop;
    $shop->refresh();
    $order->update([
        'updated_at' => Date::now()->subDays(40)->toDateString()
    ]);

    return $order;
});

test('create purge', function (Order $order) {
    $shop = $order->shop;
    $purge = StorePurge::make()->action($shop, [
        'type' => PurgeTypeEnum::MANUAL,
        'scheduled_at' => now(),
        'inactive_days' => 30,
    ]);

    expect($purge)->toBeInstanceOf(Purge::class)
        ->and($purge->type)->toBe(PurgeTypeEnum::MANUAL)
        ->and($purge->stats->estimated_number_orders)->toBe(1);

    return $purge;
})->depends('create old order');

test('update purge', function (Purge $purge) {
    $newSchedule = Date::now()->addDays(5);
    $purge = UpdatePurge::make()->action($purge, [
        'scheduled_at' => $newSchedule
    ]);

    expect($purge)->toBeInstanceOf(Purge::class)
        ->and(Carbon::parse($purge->scheduled_at)->toDateString())->toBe($newSchedule->toDateString());

    return $purge;
})->depends('create purge');

test('update purge order', function (Purge $purge) {
    $purgedOrder = $purge->purgedOrders->first();
    $updatedPurgedOrder = UpdatePurgedOrder::make()->action($purgedOrder, [
        'error_message' => 'error test'
    ]);

    expect($updatedPurgedOrder)->toBeInstanceOf(PurgedOrder::class)
        ->and($updatedPurgedOrder->error_message)->toBe('error test');

    return $updatedPurgedOrder;
})->depends('create purge');

test('delete transaction', function (Order $order) {
    $transaction = $order->transactions->first();

    $deletedTransaction = DeleteTransaction::make()->action($order, $transaction);
    $order->refresh();

    expect($order->transactions()->count())->toBe(0);
    return $order;
})->depends('create old order');
