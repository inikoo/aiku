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
use App\Actions\Procurement\StockDelivery\ExportStockDeliveries;
use App\Actions\Procurement\StockDelivery\UI\CreateStockDelivery;
use App\Actions\Procurement\StockDelivery\UI\EditStockDelivery;
use App\Actions\Procurement\StockDelivery\UI\IndexStockDeliveries;
use App\Actions\Procurement\StockDelivery\UI\ShowStockDelivery;
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


Route::get('/stock-deliveries/export', ExportStockDeliveries::class)->name('stock-deliveries.export');

Route::get('/stock-deliveries', IndexStockDeliveries::class)->name('stock-deliveries.index');
Route::get('/stock-deliveries/create', CreateStockDelivery::class)->name('stock-deliveries.create');
Route::get('/stock-deliveries/{stockDelivery}', ShowStockDelivery::class)->name('stock-deliveries.show');
Route::get('/stock-deliveries/{stockDelivery}/edit', EditStockDelivery::class)->name('stock-deliveries.edit');
