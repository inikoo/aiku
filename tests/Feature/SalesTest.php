<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:03:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Accounting\Invoice\StoreInvoice;
use App\Actions\Accounting\Invoice\UpdateInvoice;
use App\Actions\Accounting\Payment\StorePayment;
use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\Accounting\PaymentAccount\UpdatePaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\UpdatePaymentServiceProvider;
use App\Actions\Dropshipping\CustomerClient\StoreCustomerClient;
use App\Actions\Dropshipping\CustomerClient\UpdateCustomerClient;
use App\Actions\Leads\Prospect\StoreProspect;
use App\Actions\Leads\Prospect\UpdateProspect;
use App\Actions\Marketing\ShippingZone\StoreShippingZone;
use App\Actions\Marketing\ShippingZone\UpdateShippingZone;
use App\Actions\Marketing\ShippingZoneSchema\StoreShippingZoneSchema;
use App\Actions\Marketing\ShippingZoneSchema\UpdateShippingZoneSchema;
use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Sales\Customer\StoreCustomer;
use App\Actions\Sales\Order\DeleteOrder;
use App\Actions\Sales\Order\StoreOrder;
use App\Actions\Sales\Order\UpdateOrder;
use App\Actions\Sales\Order\UpdateStateToCreatingOrder;
use App\Actions\Sales\Order\UpdateStateToFinalizedOrder;
use App\Actions\Sales\Order\UpdateStateToHandlingOrder;
use App\Actions\Sales\Order\UpdateStateToPackedOrder;
use App\Actions\Sales\Order\UpdateStateToSettledOrder;
use App\Actions\Sales\Order\UpdateStateToSubmittedOrder;
use App\Actions\Sales\Transaction\StoreTransaction;
use App\Actions\Sales\Transaction\UpdateTransaction;
use App\Actions\Tenancy\Group\StoreGroup;
use App\Actions\Tenancy\Tenant\StoreTenant;
use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderTypeEnum;
use App\Enums\Sales\Customer\CustomerStatusEnum;
use App\Enums\Sales\Order\OrderStateEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Helpers\Address;
use App\Models\Leads\Prospect;
use App\Models\Marketing\ShippingZone;
use App\Models\Marketing\ShippingZoneSchema;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use App\Models\Sales\Transaction;
use App\Models\Tenancy\Group;
use App\Models\Tenancy\Tenant;
use Illuminate\Validation\ValidationException;

beforeAll(function () {
    loadDB('test_base_database.dump');
});


beforeEach(function () {
    $tenant = Tenant::first();
    if (!$tenant) {
        $group  = StoreGroup::make()->asAction(Group::factory()->definition());
        $tenant = StoreTenant::make()->action($group, Tenant::factory()->definition());
    }
    $tenant->makeCurrent();
});

test('create shop', function () {
    $shop = StoreShop::make()->action(Shop::factory()->definition());
    expect($shop)->toBeInstanceOf(Shop::class);
    return $shop;
});

test('create prospect', function ($shop) {
    $prospect = StoreProspect::make()->action($shop, [
        'contact_name'  => 'check123',
        'company_name'  => 'check123',
        'email'         => 'test@gmail.com',
        'phone'         => '+12345678',
        'website'       => 'https://google.com'
    ], Address::factory()->definition());
    $this->assertModelExists($prospect);
    return $prospect;
})->depends('create shop');


test('update prospect', function () {
    $prospect        = Prospect::latest()->first();
    $prospectUpdated = UpdateProspect::run($prospect, Prospect::factory()->definition());
    $this->assertModelExists($prospectUpdated);
})->depends('create prospect');


test('create customer', function ($shop) {
    try {
        $customer = StoreCustomer::make()->action(
            $shop,
            Customer::factory()->definition(),
            Address::factory()->definition()
        );


    } catch (Throwable) {
        $customer=null;
    }

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->reference)->toBe('000001')
        ->and($customer->status)->toBe(CustomerStatusEnum::APPROVED);

    return $customer;


})->depends('create shop');

test('create other customer', function ($shop) {
    try {
        $customer = StoreCustomer::make()->action(
            $shop,
            Customer::factory()->definition(),
            Address::factory()->definition()
        );
    } catch (Throwable) {
        $customer=null;
    }
    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->reference)->toBe('000002')
        ->and($customer->status)->toBe(CustomerStatusEnum::APPROVED);

    return $customer;
})->depends('create shop');


test('create shipping zone schema', function ($shop) {
    $shippingZoneSchema = StoreShippingZoneSchema::make()->action($shop, ShippingZoneSchema::factory()->definition());
    $this->assertModelExists($shop);

    return $shippingZoneSchema;
})->depends('create shop');

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
    $shipmentAddress = Address::latest()->first();
    $order           = StoreOrder::make()->action($customer, Order::factory()->definition(), $billingAddress, $shipmentAddress);

    expect($order)->toBeInstanceOf(Order::class);

    return $order;
})->depends('create customer');


test('create transaction', function ($order) {
    $transaction = StoreTransaction::make()->action($order, Transaction::factory()->definition());

    $this->assertModelExists($transaction);

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
    $shop           = StoreShop::make()->action(Shop::factory()->definition());
    $customer       = StoreCustomer::make()->action($shop, Customer::factory()->definition(), Address::factory()->definition(), );
    $customerClient = StoreCustomerClient::make()->action($customer, CustomerClient::factory()->definition(), Address::factory()->definition(), );
    $this->assertModelExists($customerClient);
    expect($customerClient->shop->code)->toBe($shop->code)
        ->and($customerClient->customer->reference)->toBe($customer->reference);
    return $customerClient;
});

test('update customer client', function ($customerClient) {
    $customerClient = UpdateCustomerClient::make()->action($customerClient, ['reference' => '001']);
    expect($customerClient->reference)->toBe('001');
})->depends('create customer client');

test('create payment service provider', function () {
    $paymentServiceProvider = StorePaymentServiceProvider::make()->action(PaymentServiceProvider::factory()->definition());
    $this->assertModelExists($paymentServiceProvider);

    return $paymentServiceProvider;
});

test('can not update payment service provider type', function ($paymentServiceProvider) {
    $paymentServiceProvider = UpdatePaymentServiceProvider::make()->action($paymentServiceProvider, ['type' => PaymentServiceProviderTypeEnum::BANK->value]);
    expect($paymentServiceProvider->type)->not->toBe(PaymentServiceProviderTypeEnum::BANK->value);
})->depends('create payment service provider');

test('update payment service provider code', function ($paymentServiceProvider) {
    $paymentServiceProvider = UpdatePaymentServiceProvider::make()->action($paymentServiceProvider, ['code' => 'hello']);
    expect($paymentServiceProvider->code)->toBe('hello');
})->depends('create payment service provider');

test('create payment account', function ($paymentServiceProvider) {
    $paymentAccount = StorePaymentAccount::make()->action($paymentServiceProvider, PaymentAccount::factory()->definition());
    $this->assertModelExists($paymentAccount);

    return $paymentAccount;
})->depends('create payment service provider');

test('update payment account', function ($paymentAccount) {
    $paymentAccount = UpdatePaymentAccount::make()->action($paymentAccount, ['name' => 'Pika Ltd']);
    expect($paymentAccount->name)->toBe('Pika Ltd');
})->depends('create payment account');


test(
    'create payment',
    function ($paymentAccount) {
        $shop     = StoreShop::make()->action(Shop::factory()->definition());
        $customer = StoreCustomer::make()->action(
            $shop,
            Customer::factory()->definition(),
            Address::factory()->definition()
        );
        $payment  = StorePayment::make()->action($customer, $paymentAccount, Payment::factory()->definition());
        $this->assertModelExists($payment);

        return $payment;
    }
)->depends('create payment account');

test('create invoice', function ($customer) {
    $invoice = StoreInvoice::make()->action($customer, Invoice::factory()->definition(), Address::first());
    expect($invoice->number)->toBe(00001);

    return $invoice;
})->depends('create customer');

test('update invoice', function ($invoice) {
    $invoice = UpdateInvoice::make()->action($invoice, Invoice::factory()->definition());
    expect($invoice->number)->toBe(00001);
})->depends('create invoice');
