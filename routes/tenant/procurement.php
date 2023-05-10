<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Procurement\Agent\UI\CreateAgent;
use App\Actions\Procurement\Agent\UI\EditAgent;
use App\Actions\Procurement\Agent\UI\IndexAgents;
use App\Actions\Procurement\Agent\UI\ShowAgent;
use App\Actions\Procurement\Marketplace\Agent\UI\CreateMarketplaceAgent;
use App\Actions\Procurement\Marketplace\Agent\UI\IndexMarketplaceAgents;
use App\Actions\Procurement\Marketplace\Agent\UI\ShowMarketplaceAgent;
use App\Actions\Procurement\Marketplace\Supplier\UI\CreateMarketplaceSupplier;
use App\Actions\Procurement\Marketplace\Supplier\UI\IndexMarketplaceSuppliers;
use App\Actions\Procurement\Marketplace\Supplier\UI\ShowMarketplaceSupplier;
use App\Actions\Procurement\PurchaseOrder\UI\CreatePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UI\EditPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UI\IndexPurchaseOrders;
use App\Actions\Procurement\PurchaseOrder\UI\ShowPurchaseOrder;
use App\Actions\Procurement\Supplier\UI\CreateSupplier;
use App\Actions\Procurement\Supplier\UI\EditSupplier;
use App\Actions\Procurement\Supplier\UI\IndexSuppliers;
use App\Actions\Procurement\Supplier\UI\ShowSupplier;
use App\Actions\Procurement\SupplierDelivery\UI\CreateSupplierDelivery;
use App\Actions\Procurement\SupplierDelivery\UI\EditSupplierDelivery;
use App\Actions\Procurement\SupplierDelivery\UI\IndexSupplierDeliveries;
use App\Actions\Procurement\SupplierDelivery\UI\ShowSupplierDelivery;
use App\Actions\Procurement\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\Procurement\SupplierProduct\UI\ShowSupplierProduct;
use App\Actions\UI\Procurement\ProcurementDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ProcurementDashboard::class)->name('dashboard');
Route::get('/suppliers', IndexSuppliers::class)->name('suppliers.index');
Route::get('/suppliers/create', CreateSupplier::class)->name('suppliers.create');
Route::get('/suppliers/{supplier}', ShowSupplier::class)->name('suppliers.show');
Route::get('/suppliers/{supplier}/edit', EditSupplier::class)->name('suppliers.edit');

Route::get('/agents', IndexAgents::class)->name('agents.index');
Route::get('/agents/create', CreateAgent::class)->name('agents.create');
Route::get('/agents/{agent}', ShowAgent::class)->name('agents.show');
Route::get('/agents/{agent}/edit', EditAgent::class)->name('agents.edit');

Route::get('/agents/{agent}/suppliers', [IndexSuppliers::class, 'inAgent'])->name('agents.show.suppliers.index');
Route::get('/agents/{agent}/suppliers/{supplier}', [ShowSupplier::class, 'inAgent'])->name('agents.show.suppliers.show');

Route::get('/supplier-products', IndexSupplierProducts::class)->name('supplier-products.index');
Route::get('/supplier-products/{supplierProduct}', ShowSupplierProduct::class)->name('supplier-products.show');

Route::get('/purchase-orders', IndexPurchaseOrders::class)->name('purchase-orders.index');
Route::get('/purchase-orders/create', CreatePurchaseOrder::class)->name('purchase-orders.create');
Route::get('/purchase-orders/{purchaseOrder}', ShowPurchaseOrder::class)->name('purchase-orders.show');
Route::get('/purchase-orders/{purchaseOrder}/edit', EditPurchaseOrder::class)->name('purchase-orders.edit');

Route::get('/supplier-deliveries', IndexSupplierDeliveries::class)->name('supplier-deliveries.index');
Route::get('/supplier-deliveries/create', CreateSupplierDelivery::class)->name('supplier-deliveries.create');
Route::get('/supplier-deliveries/{supplierDelivery}', ShowSupplierDelivery::class)->name('supplier-deliveries.show');
Route::get('/supplier-deliveries/{supplierDelivery}/edit', EditSupplierDelivery::class)->name('supplier-deliveries.edit');

Route::get('/marketplace-agents', IndexMarketplaceAgents::class)->name('marketplace-agents.index');
Route::get('/marketplace-agents/create', CreateMarketplaceAgent::class)->name('marketplace-agents.create');
Route::get('/marketplace-agents/{marketplaceAgent}', ShowMarketplaceAgent::class)->name('marketplace-agents.show');

Route::get('/marketplace-suppliers', IndexMarketplaceSuppliers::class)->name('marketplace-suppliers.index');
Route::get('/marketplace-suppliers/create', CreateMarketplaceSupplier::class)->name('marketplace-suppliers.create');
Route::get('/marketplace-suppliers/{marketplaceSupplier}', ShowMarketplaceSupplier::class)->name('marketplace-suppliers.show');
