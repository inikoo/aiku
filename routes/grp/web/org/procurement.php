<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Procurement\OrgAgent\UI\EditAgent;
use App\Actions\Procurement\OrgAgent\UI\IndexAgents;
use App\Actions\Procurement\OrgAgent\UI\ShowOrgAgent;
use App\Actions\Procurement\PurchaseOrder\ExportPurchaseOrders;
use App\Actions\Procurement\PurchaseOrder\UI\CreatePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UI\EditPurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UI\IndexPurchaseOrders;
use App\Actions\Procurement\PurchaseOrder\UI\ShowPurchaseOrder;
use App\Actions\Procurement\Supplier\ExportSuppliers;
use App\Actions\Procurement\Supplier\UI\EditSupplier;
use App\Actions\Procurement\Supplier\UI\RemoveSupplier;
use App\Actions\Procurement\Supplier\UI\ShowSupplier;
use App\Actions\Procurement\SupplierDelivery\ExportSupplierDeliveries;
use App\Actions\Procurement\SupplierDelivery\UI\CreateSupplierDelivery;
use App\Actions\Procurement\SupplierDelivery\UI\EditSupplierDelivery;
use App\Actions\Procurement\SupplierDelivery\UI\IndexSupplierDeliveries;
use App\Actions\Procurement\SupplierDelivery\UI\ShowSupplierDelivery;
use App\Actions\Procurement\SupplierProduct\ExportSupplierProducts;
use App\Actions\Procurement\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\Procurement\SupplierProduct\UI\ShowSupplierProduct;
use App\Actions\Procurement\SupplierPurchaseOrder\UI\CreateSupplierPurchaseOrder;
use App\Actions\Procurement\SupplierPurchaseOrder\UI\EditSupplierPurchaseOrder;
use App\Actions\Procurement\SupplierPurchaseOrder\UI\IndexSupplierPurchaseOrders;
use App\Actions\Procurement\SupplierPurchaseOrder\UI\ShowSupplierPurchaseOrder;
use App\Actions\SupplyChain\Agent\ExportAgents;
use App\Actions\SupplyChain\Agent\UI\RemoveAgent;
use App\Actions\SupplyChain\Supplier\UI\IndexSuppliers;
use App\Actions\UI\Procurement\ProcurementDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ProcurementDashboard::class)->name('dashboard');

Route::get('/suppliers/export', ExportSuppliers::class)->name('suppliers.export');

Route::get('/suppliers', IndexSuppliers::class)->name('suppliers.index');
Route::get('/suppliers/{supplier}', ShowSupplier::class)->name('suppliers.show');
Route::get('/suppliers/{supplier}/edit', EditSupplier::class)->name('suppliers.edit');
Route::get('/suppliers/{supplier}/delete', RemoveSupplier::class)->name('suppliers.remove');

Route::get('/agents/export', ExportAgents::class)->name('agents.export');

Route::get('/agents', IndexAgents::class)->name('agents.index');

Route::get('/agents/{agent}', ShowOrgAgent::class)->name('agents.show');
Route::get('/agents/{agent}/edit', EditAgent::class)->name('agents.edit');
Route::get('/agents/{agent}/delete', RemoveAgent::class)->name('agents.remove');

Route::get('/agents/{agent}/suppliers', [IndexSuppliers::class, 'inAgent'])->name('agents.show.suppliers.index');
Route::get('/agents/{agent}/suppliers/{supplier}', [ShowSupplier::class, 'inAgent'])->name('agents.show.suppliers.show');
Route::get('/agents/{agent}/suppliers/{supplier}/edit', [EditSupplier::class, 'inAgent'])->name('agents.show.suppliers.edit');
Route::get('/agents/{agent}/supplier-products', [IndexSupplierProducts::class, 'inAgent'])->name('agents.show.supplier-products.index');
Route::get('/agents/{agent}/supplier-products/{supplierProduct}', [ShowSupplierProduct::class, 'inAgent'])->name('agents.show.supplier-products.show');
Route::get('/agents/{agent}/supplier-purchase-orders/{supplierPurchaseOrder}', ShowSupplierPurchaseOrder::class)->name('agents.show.supplier-purchase-orders.show');

Route::get('/supplier-products/export', ExportSupplierProducts::class)->name('supplier-products.export');

Route::get('/supplier-products', IndexSupplierProducts::class)->name('supplier-products.index');
Route::get('/supplier-products/{supplierProduct}', ShowSupplierProduct::class)->name('supplier-products.show');

Route::get('/purchase-orders/export', ExportPurchaseOrders::class)->name('purchase-orders.export');

Route::get('/purchase-orders', IndexPurchaseOrders::class)->name('purchase-orders.index');
Route::get('/purchase-orders/create', CreatePurchaseOrder::class)->name('purchase-orders.create');
Route::get('/suppliers/{supplier}/purchase-orders/create', [CreatePurchaseOrder::class, 'inSupplier'])->name('suppliers.show.purchase-orders.create');
Route::get('/purchase-orders/{purchaseOrder}', ShowPurchaseOrder::class)->name('purchase-orders.show');
Route::get('/purchase-orders/{purchaseOrder}/edit', EditPurchaseOrder::class)->name('purchase-orders.edit');

Route::get('/supplier-purchase-orders', IndexSupplierPurchaseOrders::class)->name('supplier-purchase-orders.index');
Route::get('/supplier-purchase-orders/create', CreateSupplierPurchaseOrder::class)->name('supplier-purchase-orders.create');
Route::get('/supplier-purchase-orders/{supplierPurchaseOrder}', ShowSupplierPurchaseOrder::class)->name('supplier-purchase-orders.show');
Route::get('/supplier-purchase-orders/{supplierPurchaseOrder}/edit', EditSupplierPurchaseOrder::class)->name('supplier-purchase-orders.edit');

Route::get('/supplier-deliveries/export', ExportSupplierDeliveries::class)->name('supplier-deliveries.export');

Route::get('/supplier-deliveries', IndexSupplierDeliveries::class)->name('supplier-deliveries.index');
Route::get('/supplier-deliveries/create', CreateSupplierDelivery::class)->name('supplier-deliveries.create');
Route::get('/supplier-deliveries/{supplierDelivery}', ShowSupplierDelivery::class)->name('supplier-deliveries.show');
Route::get('/supplier-deliveries/{supplierDelivery}/edit', EditSupplierDelivery::class)->name('supplier-deliveries.edit');


Route::prefix("marketplace")
    ->name("marketplace.")
    ->group(
        function () {
            $parent='shop';
            require __DIR__.'/procurement-marketplace.php';
        }
    );
