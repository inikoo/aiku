<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 10:25:11 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */


use App\Actions\Accounting\Payment\UpdatePayment;
use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\Accounting\PaymentAccount\UpdatePaymentAccount;
use App\Actions\Auth\Guest\StoreGuest;
use App\Actions\Auth\Guest\UpdateGuest;
use App\Actions\Auth\User\UpdateUser;
use App\Actions\HumanResources\Clocking\StoreClocking;
use App\Actions\HumanResources\Clocking\UpdateClocking;
use App\Actions\HumanResources\ClockingMachine\StoreClockingMachine;
use App\Actions\HumanResources\ClockingMachine\UpdateClockingMachine;
use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Actions\HumanResources\WorkingPlace\StoreWorkingPlace;
use App\Actions\HumanResources\WorkingPlace\UpdateWorkingPlace;
use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Inventory\Location\UpdateLocation;
use App\Actions\Inventory\Stock\UpdateStock;
use App\Actions\Inventory\StockFamily\UpdateStockFamily;
use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Actions\Inventory\Warehouse\UpdateWarehouse;
use App\Actions\Inventory\WarehouseArea\StoreWarehouseArea;
use App\Actions\Inventory\WarehouseArea\UpdateWarehouseArea;
use App\Actions\Mail\Outbox\UpdateOutbox;
use App\Actions\Market\Product\StoreProduct;
use App\Actions\Market\ProductCategory\StoreProductCategory;
use App\Actions\Market\ProductCategory\UpdateProductCategory;
use App\Actions\Market\Product\UpdateProduct;
use App\Actions\Market\Shop\StoreShop;
use App\Actions\Market\Shop\StoreShops;
use App\Actions\Market\Shop\UpdateShop;
use App\Actions\Procurement\Agent\StoreAgent;
use App\Actions\Procurement\Agent\UpdateAgent;
use App\Actions\Procurement\Marketplace\Agent\UpdateMarketplaceAgent;
use App\Actions\Procurement\Marketplace\Supplier\UpdateMarketplaceSupplier;
use App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrder;
use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Actions\Procurement\Supplier\UpdateSupplier;
use App\Actions\Procurement\SupplierDelivery\UpdateSupplierDelivery;
use App\Actions\Sales\Customer\StoreCustomer;
use App\Actions\Sales\Customer\UpdateCustomer;
use App\Actions\Sales\Order\StoreOrder;
use App\Actions\Sales\Order\UpdateOrder;
use App\Actions\UI\Profile\UpdateProfile;
use Illuminate\Support\Facades\Route;

Route::post('/shop/', StoreShop::class)->name('shop.store');
Route::post('/shops/', StoreShops::class)->name('shop.store-multi');

Route::patch('/shop/{shop}', UpdateShop::class)->name('show.update');

Route::patch('/customer/{customer}', UpdateCustomer::class)->name('customer.update');
Route::post('/shop/{shop}/customer/', StoreCustomer::class)->name('show.customer.store');
Route::post('/shop/{shop}/department/', [StoreProductCategory::class, 'inShop'])->name('show.department.store');
Route::post('/shop/{shop}/product/', [StoreProduct::class, 'inShop'])->name('show.product.store');
Route::post('/shop/{shop}/order/', [StoreOrder::class, 'inShop'])->name('show.order.store');

Route::post('/product/', StoreProduct::class)->name('product.store');
Route::patch('/product/{product}', UpdateProduct::class)->name('product.update');

Route::post('/department/', StoreProductCategory::class)->name('department.store');
Route::patch('/department/{department}', UpdateProductCategory::class)->name('department.update');

Route::post('/order/', StoreOrder::class)->name('order.store');
Route::patch('/order/{order}', UpdateOrder::class)->name('order.update');

Route::patch('/employee/{employee}', UpdateEmployee::class)->name('employee.update');
Route::post('/employee/', StoreEmployee::class)->name('employee.store');

Route::patch('/working-place/{workplace}', UpdateWorkingPlace::class)->name('working-place.update');
Route::post('/working-place/', StoreWorkingPlace::class)->name('working-place.store');

Route::patch('/clocking-machine/{clockingMachine}', UpdateClockingMachine::class)->name('clocking-machine.update');
Route::post('/clocking-machine', StoreClockingMachine::class)->name('clocking-machine.store');
Route::post('/working-place/{workplace}/clocking-machine', StoreClockingMachine::class)->name('working-place.clocking-machine.store');

Route::patch('/clocking/{clocking}', UpdateClocking::class)->name('clocking.update');
Route::post('/clocking', StoreClocking::class)->name('clocking.store');
Route::post('/working-place/{workplace}/clocking', StoreClocking::class)->name('working-place.clocking.store');
Route::post('/clocking-machine/{clockingMachine}/clocking', StoreClocking::class, 'inClockingMachine')->name('clocking-machine.clocking.store');
Route::post('/working-place/{workplace}/clocking-machine/{clockingMachine}/clocking', StoreClocking::class)->name('working-place.clocking-machine.clocking.store');

Route::post('/warehouse/', StoreWarehouse::class)->name('warehouse.store');
Route::patch('/warehouse/{warehouse}', UpdateWarehouse::class)->name('warehouse.update');

Route::post('/warehouse/{warehouse}/area/', StoreWarehouseArea::class)->name('warehouse.warehouse-area.store');

Route::patch('/area/{warehouseArea}', UpdateWarehouseArea::class)->name('warehouse-area.update');

Route::patch('/location/{location}', UpdateLocation::class)->name('location.update');

Route::post('/warehouse/{warehouse}/location', StoreLocation::class)->name('warehouse.location.store');
Route::post('/area/{warehouseArea}/location', [StoreLocation::class, 'inWarehouseArea'])->name('warehouse-area.location.store');

Route::patch('/stock/{stock}', UpdateStock::class)->name('stock.update');

Route::patch('/stock-family/{stockFamily:slug}', UpdateStockFamily::class)->name('stock-family.update');

Route::patch('/agent/{agent}', UpdateAgent::class)->name('agent.update');
Route::post('/agent/{agent}/purchase-order', [StorePurchaseOrder::class, 'inAgent'])->name('agent.purchase-order.store');

Route::post('/agent/', StoreAgent::class)->name('agent.store');

Route::patch('/supplier/{supplier}', UpdateSupplier::class)->name('supplier.update');
Route::post('/agent/{supplier}/purchase-order', [StorePurchaseOrder::class, 'inSupplier'])->name('supplier.purchase-order.store');

Route::post('/supplier/', StoreSupplier::class)->name('supplier.store');
Route::post('/agent/{agent}/supplier', [StoreSupplier::class,'inAgent'])->name('agent.supplier.store');
Route::post('/supplier/{supplier}/purchase-order', [StorePurchaseOrder::class, 'inSupplier'])->name('supplier.purchase-order.store');

Route::patch('/payment/{payment}', UpdatePayment::class)->name('payment.update');

Route::patch('/payment-account/{paymentAccount}', UpdatePaymentAccount::class)->name('payment-account.update');
Route::post('/payment-account', StorePaymentAccount::class)->name('payment-account.store');

Route::patch('/user/{user}', UpdateUser::class)->name('user.update');
Route::patch('/profile', UpdateProfile::class)->name('profile.update');


Route::patch('/guest/{guest}', UpdateGuest::class)->name('guest.update');
Route::post('/guest/', StoreGuest::class)->name('guest.store');

Route::patch('/outbox/{outbox}', UpdateOutbox::class)->name('outbox.update');

Route::patch('/purchase-order/{purchaseOrder}', UpdatePurchaseOrder::class)->name('purchase-order.update');

Route::patch('/supplier-delivery/{supplierDelivery}', UpdateSupplierDelivery::class)->name('supplier-delivery.update');

Route::patch('/marketplace-agent/{marketplaceAgent}', UpdateMarketplaceAgent::class)->name('marketplace-agent.update');

Route::patch('/marketplace-supplier/{marketplaceSupplier}', UpdateMarketplaceSupplier::class)->name('marketplace-supplier.update');
