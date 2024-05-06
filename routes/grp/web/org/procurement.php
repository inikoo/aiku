<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Procurement\OrgAgent\UI\EditOrgAgent;
use App\Actions\Procurement\OrgAgent\UI\IndexOrgAgents;
use App\Actions\Procurement\OrgAgent\UI\ShowOrgAgent;
use App\Actions\Procurement\OrgSupplier\UI\EditOrgSupplier;
use App\Actions\Procurement\OrgSupplier\UI\IndexOrgSuppliers;
use App\Actions\Procurement\OrgSupplier\UI\ShowOrgSupplier;
use App\Actions\Procurement\PurchaseOrder\ExportPurchaseOrders;
use App\Actions\Procurement\PurchaseOrder\UI\CreatePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UI\EditPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UI\IndexPurchaseOrders;
use App\Actions\Procurement\PurchaseOrder\UI\ShowPurchaseOrder;
use App\Actions\Procurement\SupplierDelivery\ExportSupplierDeliveries;
use App\Actions\Procurement\SupplierDelivery\UI\CreateSupplierDelivery;
use App\Actions\Procurement\SupplierDelivery\UI\EditSupplierDelivery;
use App\Actions\Procurement\SupplierDelivery\UI\IndexSupplierDeliveries;
use App\Actions\Procurement\SupplierDelivery\UI\ShowSupplierDelivery;
use App\Actions\Procurement\SupplierProduct\ExportSupplierProducts;
use App\Actions\Procurement\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\Procurement\SupplierProduct\UI\ShowSupplierProduct;

use App\Actions\SupplyChain\Agent\ExportAgents;
use App\Actions\SupplyChain\Agent\UI\CreateAgent;
use App\Actions\SupplyChain\Agent\UI\RemoveAgent;
use App\Actions\SupplyChain\Supplier\UI\IndexSuppliers;
use App\Actions\UI\Procurement\ProcurementDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ProcurementDashboard::class)->name('dashboard');

//Route::get('/suppliers/export', ExportSuppliers::class)->name('suppliers.export');

Route::get('/suppliers', IndexOrgSuppliers::class)->name('suppliers.index');
Route::get('/suppliers/{orgSupplier}', ShowOrgSupplier::class)->name('suppliers.show');
Route::get('/suppliers/{orgSupplier}/edit', EditOrgSupplier::class)->name('suppliers.edit');

Route::get('/agents/export', ExportAgents::class)->name('agents.export');

Route::get('/agents', IndexOrgAgents::class)->name('agents.index');
Route::get('/agents/create', CreateAgent::class)->name('agents.create');

Route::get('/agents/{orgAgent}', ShowOrgAgent::class)->name('agents.show');
Route::get('/agents/{orgAgent}/edit', EditOrgAgent::class)->name('agents.edit');
Route::get('/agents/{orgAgent}/delete', RemoveAgent::class)->name('agents.remove');

Route::get('/agents/{orgAgent}/suppliers', [IndexSuppliers::class, 'inOrgAgent'])->name('agents.show.suppliers.index');
//Route::get('/agents/{orgAgent}/suppliers/{orgSupplier}', [ShowSupplier::class, 'inOrgAgent'])->name('agents.show.suppliers.show');
//Route::get('/agents/{orgAgent}/suppliers/{orgSupplier}/edit', [EditOrgSupplier::class, 'inOrgAgent'])->name('agents.show.suppliers.edit');
Route::get('/agents/{orgAgent}/supplier-products', [IndexSupplierProducts::class, 'inOrgAgent'])->name('agents.show.supplier-products.index');
Route::get('/agents/{orgAgent}/supplier-products/{supplierProduct}', [ShowSupplierProduct::class, 'inOrgAgent'])->name('agents.show.supplier-products.show');

Route::get('/supplier-products/export', ExportSupplierProducts::class)->name('supplier-products.export');

Route::get('/supplier-products', IndexSupplierProducts::class)->name('supplier-products.index');
Route::get('/supplier-products/{supplierProduct}', ShowSupplierProduct::class)->name('supplier-products.show');

Route::get('/purchase-orders/export', ExportPurchaseOrders::class)->name('purchase-orders.export');

Route::get('/purchase-orders', IndexPurchaseOrders::class)->name('purchase-orders.index');
Route::get('/purchase-orders/create', CreatePurchaseOrder::class)->name('purchase-orders.create');
Route::get('/suppliers/{orgSupplier}/purchase-orders/create', [CreatePurchaseOrder::class, 'inSupplier'])->name('suppliers.show.purchase-orders.create');
Route::get('/purchase-orders/{purchaseOrder}', ShowPurchaseOrder::class)->name('purchase-orders.show');
Route::get('/purchase-orders/{purchaseOrder}/edit', EditPurchaseOrder::class)->name('purchase-orders.edit');


Route::get('/supplier-deliveries/export', ExportSupplierDeliveries::class)->name('supplier-deliveries.export');

Route::get('/supplier-deliveries', IndexSupplierDeliveries::class)->name('supplier-deliveries.index');
Route::get('/supplier-deliveries/create', CreateSupplierDelivery::class)->name('supplier-deliveries.create');
Route::get('/supplier-deliveries/{supplierDelivery}', ShowSupplierDelivery::class)->name('supplier-deliveries.show');
Route::get('/supplier-deliveries/{supplierDelivery}/edit', EditSupplierDelivery::class)->name('supplier-deliveries.edit');
