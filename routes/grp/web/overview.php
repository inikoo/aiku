<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Apr 2024 14:12:41 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Accounting\Invoice\UI\IndexInvoices;
use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Accounting\PaymentAccount\UI\IndexPaymentAccounts;
use App\Actions\Catalogue\Collection\UI\IndexCollection;
use App\Actions\Accounting\UI\IndexCustomerBalances;
use App\Actions\Billables\Charge\UI\IndexCharges;
use App\Actions\Billables\Service\UI\IndexServices;
use App\Actions\Catalogue\Product\UI\IndexProducts;
use App\Actions\Catalogue\ProductCategory\UI\IndexDepartments;
use App\Actions\Catalogue\ProductCategory\UI\IndexFamilies;
use App\Actions\Comms\Mailshot\UI\IndexMarketingMailshots;
use App\Actions\Comms\Mailshot\UI\IndexNewsletterMailshots;
use App\Actions\Comms\Outbox\UI\IndexOutboxes;
use App\Actions\Comms\PostRoom\UI\IndexPostRooms;
use App\Actions\Comms\PostRoom\UI\ShowPostRoom;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\CRM\Prospect\UI\IndexProspects;
use App\Actions\Discounts\Offer\UI\IndexOffers;
use App\Actions\Discounts\OfferCampaign\UI\IndexOfferCampaigns;
use App\Actions\Dispatching\DeliveryNote\UI\IndexDeliveryNotes;
use App\Actions\HumanResources\ClockingMachine\UI\IndexClockingMachines;
use App\Actions\HumanResources\Employee\UI\IndexEmployees;
use App\Actions\HumanResources\JobPosition\UI\IndexJobPositions;
use App\Actions\HumanResources\Timesheet\UI\IndexTimesheets;
use App\Actions\HumanResources\Workplace\UI\IndexWorkplaces;
use App\Actions\Ordering\Order\UI\IndexOrders;
use App\Actions\Ordering\Purge\UI\IndexPurges;
use App\Actions\Ordering\ShippingZoneSchema\UI\IndexShippingZoneSchemas;
use App\Actions\Procurement\PurchaseOrder\UI\IndexPurchaseOrders;
use App\Actions\Production\Artefact\UI\IndexArtefacts;
use App\Actions\Production\ManufactureTask\UI\IndexManufactureTasks;
use App\Actions\Production\RawMaterial\UI\IndexRawMaterials;
use App\Actions\SupplyChain\Agent\UI\IndexAgents;
use App\Actions\SupplyChain\Supplier\UI\IndexSuppliers;
use App\Actions\SupplyChain\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\SysAdmin\Group\UI\ShowOverviewHub;
use App\Actions\Web\Banner\UI\IndexBanners;
use App\Actions\Web\Webpage\UI\IndexWebpages;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowOverviewHub::class)->name('hub');

Route::name('comms.')->prefix('comms')->group(function () {
    Route::get('/post-rooms', IndexPostRooms::class)->name('post-rooms.index');
    Route::get('/post-rooms/{postRoom}', ShowPostRoom::class)->name('post-rooms.show');
    Route::get('/outboxes', [IndexOutboxes::class, 'inGroup'])->name('outboxes.index');
});

Route::name('catalogue.')->prefix('catalogue')->group(function () {
    Route::get('/products', [IndexProducts::class, 'inGroup'])->name('products.index');
    Route::get('/departments', [IndexDepartments::class, 'inGroup'])->name('departments.index');
    Route::get('/families', [IndexFamilies::class, 'inGroup'])->name('families.index');
    Route::get('/collections', [IndexCollection::class, 'inGroup'])->name('collections.index');
});

Route::name('billables.')->prefix('billables')->group(function () {
    Route::get('/shipping', [IndexShippingZoneSchemas::class, 'inGroup'])->name('shipping.index');
    Route::get('/charges', [IndexCharges::class, 'inGroup'])->name('charges.index');
    Route::get('/services', [IndexServices::class, 'inGroup'])->name('services.index');
});

Route::name('offer.')->prefix('offer')->group(function () {
    Route::get('/campaigns', [IndexOfferCampaigns::class, 'inGroup'])->name('campaigns.index');
    Route::get('/offers', [IndexOffers::class, 'inGroup'])->name('offers.index');
});

Route::name('marketing.')->prefix('marketing')->group(function () {
    Route::get('/newsletters', [IndexNewsletterMailshots::class, 'inGroup'])->name('newsletters.index');
    Route::get('/mailshots', [IndexMarketingMailshots::class, 'inGroup'])->name('mailshots.index');
});

Route::name('web.')->prefix('web')->group(function () {
    Route::get('/webpages', [IndexWebpages::class, 'inGroup'])->name('webpages.index');
    Route::get('/banners', [IndexBanners::class, 'inGroup'])->name('banners.index');
});

Route::name('crm.')->prefix('crm')->group(function () {
    Route::get('/customers', [IndexCustomers::class, 'inGroup'])->name('customers.index');
    Route::get('/prospects', [IndexProspects::class, 'inGroup'])->name('prospects.index');
});

Route::name('order.')->prefix('order')->group(function () {
    Route::get('/orders', [IndexOrders::class, 'inGroup'])->name('orders.index');
    Route::get('/purges', [IndexPurges::class, 'inGroup'])->name('purges.index');
    Route::get('/delivery-notes', [IndexDeliveryNotes::class, 'inGroup'])->name('delivery-notes.index');
});

Route::name('production.')->prefix('production')->group(function () {
    Route::get('/raw-materials', [IndexRawMaterials::class, 'inGroup'])->name('raw-materials.index');
    Route::get('/artefacts', [IndexArtefacts::class, 'inGroup'])->name('artefacts.index');
    Route::get('/manufacture-tasks', [IndexManufactureTasks::class, 'inGroup'])->name('manufacture-tasks.index');
    // Route::get('/job-orders', [IndexJoborder::class, 'inGroup'])->name('job-orders.index');
    // Route::get('/artisans', [IndexJoborder::class, 'inGroup'])->name('artisans.index');
});

Route::name('procurement.')->prefix('procurement')->group(function () {
    Route::get('/agents', [IndexAgents::class, 'inOverview'])->name('agents.index');
    Route::get('/suppliers', [IndexSuppliers::class, 'inOverview'])->name('suppliers.index');
    Route::get('/supplier-products', [IndexSupplierProducts::class, 'inOverview'])->name('supplier-products.index');
    Route::get('/purchase-orders', [IndexPurchaseOrders::class, 'inGroup'])->name('purchase-orders.index');
});

Route::name('accounting.')->prefix('accounting')->group(function () {
    Route::get('/invoices', [IndexInvoices::class, 'inGroup'])->name('invoices.index');
    Route::get('/payment-accounts', [IndexPaymentAccounts::class, 'inGroup'])->name('payment-accounts.index');
    Route::get('/payments', [IndexPayments::class, 'inGroup'])->name('payments.index');
    Route::get('/customer-balances', [IndexCustomerBalances::class, 'inGroup'])->name('customer-balances.index');
});


Route::name('human-resources.')->prefix('human-resources')->group(function () {
    Route::get('/workplaces', [IndexWorkplaces::class, 'inGroup'])->name('workplaces.index');
    Route::get('/responsibilities', [IndexJobPositions::class, 'inGroup'])->name('responsibilities.index');
    Route::get('/employees', [IndexEmployees::class, 'inGroup'])->name('employees.index');
    Route::get('/clocking-machines', [IndexClockingMachines::class, 'inGroup'])->name('clocking-machines.index');
    Route::get('/timesheets', [IndexTimesheets::class, 'inGroup'])->name('timesheets.index');
});
