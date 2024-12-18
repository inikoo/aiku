<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Apr 2024 14:12:41 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Invoice\UI\IndexInvoices;
use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Accounting\PaymentAccount\UI\IndexPaymentAccounts;
use App\Actions\Accounting\PaymentServiceProvider\UI\IndexPaymentServiceProviders;
use App\Actions\Accounting\UI\IndexCustomerBalances;
use App\Actions\Catalogue\Product\UI\IndexProducts;
use App\Actions\Catalogue\Product\UI\ShowProduct;
use App\Actions\Catalogue\ProductCategory\UI\IndexDepartments;
use App\Actions\Comms\Outbox\UI\IndexOutboxes;
use App\Actions\Comms\PostRoom\UI\IndexPostRooms;
use App\Actions\Comms\PostRoom\UI\ShowPostRoom;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\Dispatching\DeliveryNote\UI\IndexDeliveryNotes;
use App\Actions\HumanResources\ClockingMachine\UI\IndexClockingMachines;
use App\Actions\HumanResources\Employee\UI\IndexEmployees;
use App\Actions\HumanResources\JobPosition\UI\IndexJobPositions;
use App\Actions\HumanResources\Timesheet\UI\IndexTimesheets;
use App\Actions\HumanResources\Workplace\UI\IndexWorkplaces;
use App\Actions\Ordering\Order\UI\IndexOrders;
use App\Actions\Ordering\Purge\UI\IndexPurges;
use App\Actions\Procurement\PurchaseOrder\UI\IndexPurchaseOrders;
use App\Actions\SupplyChain\Agent\UI\IndexAgents;
use App\Actions\SupplyChain\Supplier\UI\IndexSuppliers;
use App\Actions\SupplyChain\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\SysAdmin\Group\UI\ShowOverviewHub;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowOverviewHub::class)->name('hub');

Route::name('comms.')->prefix('comms')->group(function () {
    Route::get('/post-rooms', IndexPostRooms::class)->name('post-rooms.index');
    Route::get('/post-rooms/{postRoom}', ShowPostRoom::class)->name('post-rooms.show');
    Route::get('/outboxes', [IndexOutboxes::class, 'inGroup'])->name('outboxes.index');
});

Route::name('crm.')->prefix('crm')->group(function () {
    Route::get('/customers', [IndexCustomers::class, 'inGroup'])->name('customers.index');
});

Route::name('catalogue.')->prefix('catalogue')->group(function () {
    Route::get('/products', [IndexProducts::class, 'inGroup'])->name('products.index');
    Route::get('/departments', [IndexDepartments::class, 'inGroup'])->name('departments.index');
});

// Route::get('/products/{product}', [ShowProduct::class, 'inGroup'])->name('products.show');
// Route::get('/customers/{customer}', [ShowCustomer::class, 'inGroup'])->name('customers.show');
// Route::get('/accounting/providers', IndexPaymentServiceProviders::class)->name('accounting.payment-service-providers.index');

Route::name('order.')->prefix('order')->group(function () {
    Route::post('/orders', [IndexOrders::class, 'inGroup'])->name('orders.index');
    Route::post('/purges', [IndexPurges::class, 'inGroup'])->name('purges.index');
    Route::post('/delivery-notes', [IndexDeliveryNotes::class, 'inGroup'])->name('delivery-notes.index');
});

Route::name('procurement.')->prefix('procurement')->group(function () {
    Route::post('/agents', [IndexAgents::class, 'inOverview'])->name('agents.index');
    Route::post('/suppliers', [IndexSuppliers::class, 'inOverview'])->name('suppliers.index');
    Route::post('/supplier-products', [IndexSupplierProducts::class, 'inOverview'])->name('supplier-products.index');
    Route::post('/purchase-orders', [IndexPurchaseOrders::class, 'inGroup'])->name('purchase-orders.index');
});

Route::name('accounting.')->prefix('accounting')->group(function () {
    Route::post('/invoices', [IndexInvoices::class, 'inGroup'])->name('invoices.index');
    Route::post('/payment-accounts', [IndexPaymentAccounts::class, 'inGroup'])->name('payment-accounts.index');
    Route::post('/payments', [IndexPayments::class, 'inGroup'])->name('payments.index');
    Route::post('/customer-balances', [IndexCustomerBalances::class, 'inGroup'])->name('customer-balances.index');
});

Route::name('human-resources.')->prefix('human-resources')->group(function () {
    Route::post('/workplaces', [IndexWorkplaces::class, 'inGroup'])->name('workplaces.index');
    Route::post('/responsibilities', [IndexJobPositions::class, 'inGroup'])->name('responsibilities.index');
    Route::post('/employees', [IndexEmployees::class, 'inGroup'])->name('employees.index');
    Route::post('/clocking-machines', [IndexClockingMachines::class, 'inGroup'])->name('clocking-machines.index');
    Route::post('/timesheets', [IndexTimesheets::class, 'inGroup'])->name('timesheets.index');
});