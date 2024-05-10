<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:03:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Accounting\Invoice\StoreInvoice;
use App\Actions\Accounting\Invoice\UpdateInvoice;
use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\Dropshipping\CustomerClient\StoreCustomerClient;
use App\Actions\Dropshipping\CustomerClient\UpdateCustomerClient;
use App\Actions\Market\ShippingZone\StoreShippingZone;
use App\Actions\Market\ShippingZone\UpdateShippingZone;
use App\Actions\Market\ShippingZoneSchema\StoreShippingZoneSchema;
use App\Actions\Market\ShippingZoneSchema\UpdateShippingZoneSchema;
use App\Actions\Market\Shop\StoreShop;
use App\Actions\OMS\Order\DeleteOrder;
use App\Actions\OMS\Order\StoreOrder;
use App\Actions\OMS\Order\UpdateOrder;
use App\Actions\OMS\Order\UpdateStateToCreatingOrder;
use App\Actions\OMS\Order\UpdateStateToFinalizedOrder;
use App\Actions\OMS\Order\UpdateStateToHandlingOrder;
use App\Actions\OMS\Order\UpdateStateToPackedOrder;
use App\Actions\OMS\Order\UpdateStateToSettledOrder;
use App\Actions\OMS\Order\UpdateStateToSubmittedOrder;
use App\Actions\OMS\Transaction\StoreTransaction;
use App\Actions\OMS\Transaction\UpdateTransaction;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Enums\OMS\Order\OrderStateEnum;
use App\Models\Accounting\Invoice;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Helpers\Address;
use App\Models\Market\ShippingZone;
use App\Models\Market\ShippingZoneSchema;
use App\Models\Market\Shop;
use App\Models\OMS\Order;
use App\Models\OMS\Transaction;
use Illuminate\Validation\ValidationException;

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    list(
        $this->organisation,
        $this->user,
        $this->shop
    ) = createShop();
});


test('create customer', function () {
    $customer = StoreCustomer::make()->action(
        $this->shop,
        Customer::factory()->definition(),
    );

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->reference)->toBe('000001')
        ->and($customer->status)->toBe(CustomerStatusEnum::APPROVED);

    return $customer;
});

test('create other customer', function () {
    try {
        $customer = StoreCustomer::make()->action(
            $this->shop,
            Customer::factory()->definition()
        );
    } catch (Throwable) {
        $customer = null;
    }
    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->reference)->toBe('000002')
        ->and($customer->status)->toBe(CustomerStatusEnum::APPROVED);

    return $customer;
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


test('create order', function ($customer) {
    $billingAddress  = Address::first();
    $deliveryAddress = Address::latest()->first();

    $modelData=Order::factory()->definition();
    data_set($modelData, 'billing_address', $billingAddress);
    data_set($modelData, 'delivery_address', $deliveryAddress);

    $order           = StoreOrder::make()->action($customer, $modelData);
    expect($order)->toBeInstanceOf(Order::class);

    return $order;
})->depends('create customer');


test('create transaction', function ($order) {
    $transaction = StoreTransaction::make()->action($order, Transaction::factory()->definition());

    $this->assertModelExists($transaction);

    return $transaction;
})->depends('create order')->todo();

test('update transaction', function ($transaction) {
    $order = UpdateTransaction::make()->action($transaction, Transaction::factory()->definition());

    $this->assertModelExists($order);
})->depends('create transaction');


test('update order', function ($order) {
    $order = UpdateOrder::make()->action($order, Order::factory()->definition());

    $this->assertModelExists($order);
})->depends('create order');

test('update state to submit from creating order', function ($order) {
    try {
        $order = UpdateStateToSubmittedOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toEqual(OrderStateEnum::SUBMITTED);
})->depends('create order');

test('update state to handling from submit order', function ($order) {
    try {
        $order = UpdateStateToHandlingOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toEqual(OrderStateEnum::HANDLING);
})->depends('create order');

test('update state to packed from handling', function ($order) {
    try {
        $order = UpdateStateToPackedOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toBe(OrderStateEnum::PACKED);
})->depends('create order');

test('update state to finalized from handling', function ($order) {
    try {
        $order = UpdateStateToFinalizedOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toBe(OrderStateEnum::FINALISED);
})->depends('create order');

test('update state to settled from finalized', function ($order) {
    try {
        $order = UpdateStateToSettledOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toBe(OrderStateEnum::SETTLED);
})->depends('create order');

test('update state to finalized from settled', function ($order) {
    try {
        $order = UpdateStateToFinalizedOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toBe(OrderStateEnum::FINALISED);
})->depends('create order');

test('update state to packed from finalized', function ($order) {
    try {
        $order = UpdateStateToPackedOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toBe(OrderStateEnum::PACKED);
})->depends('create order');

test('update state to handling from packed', function ($order) {
    try {
        $order = UpdateStateToHandlingOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toEqual(OrderStateEnum::HANDLING);
})->depends('create order');

test('update state to submit from handling', function ($order) {
    try {
        $order = UpdateStateToSubmittedOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toEqual(OrderStateEnum::SUBMITTED);
})->depends('create order');

test('update state to creating from submitted', function ($order) {
    try {
        $order = UpdateStateToCreatingOrder::make()->action($order);
    } catch (ValidationException) {
    }

    expect($order->state)->toEqual(OrderStateEnum::CREATING);
})->depends('create order');

test('delete order', function ($order) {
    $order = DeleteOrder::run($order);

    $this->assertSoftDeleted($order);
})->depends('create order');

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

test('create invoice from customer', function ($customer) {
    $invoiceData = Invoice::factory()->definition();
    data_set($invoiceData, 'billing_address', Address::first());
    $invoice = StoreInvoice::make()->action($customer, $invoiceData);
    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($invoice->customer)->toBeInstanceOf(Customer::class)
        ->and($invoice->number)->toBe('00001')
        ->and($invoice->customer->stats->number_invoices)->toBe(1);

    return $invoice;
})->depends('create customer');

test('update invoice from customer', function ($invoice) {
    $invoice = UpdateInvoice::make()->action($invoice, [
        'number' => '00001a'

    ]);
    expect($invoice->number)->toBe('00001a');
})->depends('create invoice from customer');

test('create invoice from order', function (Order $order) {
    $invoiceData = Invoice::factory()->definition();
    data_set($invoiceData, 'billing_address', Address::first());
    data_set($invoiceData, 'number', '00002');
    $invoice = StoreInvoice::make()->action($order, $invoiceData);
    $customer=$invoice->customer;
    $this->shop->refresh();
    expect($invoice)->toBeInstanceOf(Invoice::class)
        ->and($customer)->toBeInstanceOf(Customer::class)
        ->and($invoice->number)->toBe('00002')
        ->and($customer->stats->number_invoices)->toBe(2)
        ->and($this->shop->salesStats->number_invoices)->toBe(2);
    ;


    return $invoice;
})->depends('create order');
