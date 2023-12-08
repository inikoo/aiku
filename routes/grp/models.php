<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 10:25:11 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */


use App\Actions\Accounting\Payment\UpdatePayment;
use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\Accounting\PaymentAccount\UpdatePaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\DeletePaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\StorePaymentServiceProvider;
use App\Actions\Accounting\PaymentServiceProvider\UpdatePaymentServiceProvider;
use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\Customer\UpdateCustomer;
use App\Actions\CRM\WebUser\UpdateWebUser;
use App\Actions\Fulfilment\StoredItem\StoreStoredItem;
use App\Actions\Fulfilment\StoredItem\UpdateStoredItem;
use App\Actions\HumanResources\Clocking\DeleteClocking;
use App\Actions\HumanResources\Clocking\StoreClocking;
use App\Actions\HumanResources\Clocking\UpdateClocking;
use App\Actions\HumanResources\ClockingMachine\DeleteClockingMachine;
use App\Actions\HumanResources\ClockingMachine\StoreClockingMachine;
use App\Actions\HumanResources\ClockingMachine\UpdateClockingMachine;
use App\Actions\HumanResources\Employee\DeleteEmployee;
use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Actions\HumanResources\JobPosition\DeleteJobPosition;
use App\Actions\HumanResources\JobPosition\StoreJobPosition;
use App\Actions\HumanResources\JobPosition\UpdateJobPosition;
use App\Actions\HumanResources\WorkingPlace\DeleteWorkingPlace;
use App\Actions\HumanResources\WorkingPlace\StoreWorkingPlace;
use App\Actions\HumanResources\WorkingPlace\UpdateWorkingPlace;
use App\Actions\Inventory\Location\DeleteLocation;
use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Inventory\Location\UpdateLocation;
use App\Actions\Inventory\Stock\DeleteStock;
use App\Actions\Inventory\Stock\StoreStock;
use App\Actions\Inventory\Stock\UpdateStock;
use App\Actions\Inventory\StockFamily\DeleteStockFamily;
use App\Actions\Inventory\StockFamily\StoreStockFamily;
use App\Actions\Inventory\StockFamily\UpdateStockFamily;
use App\Actions\Inventory\Warehouse\DeleteWarehouse;
use App\Actions\Inventory\Warehouse\StoreWarehouse;
use App\Actions\Inventory\Warehouse\UpdateWarehouse;
use App\Actions\Inventory\WarehouseArea\DeleteWarehouseArea;
use App\Actions\Inventory\WarehouseArea\StoreWarehouseArea;
use App\Actions\Inventory\WarehouseArea\StoreWarehouseAreas;
use App\Actions\Inventory\WarehouseArea\UpdateWarehouseArea;
use App\Actions\Mail\Outbox\UpdateOutbox;
use App\Actions\Market\Product\DeleteProduct;
use App\Actions\Market\Product\StoreProduct;
use App\Actions\Market\Product\UpdateProduct;
use App\Actions\Market\ProductCategory\DeleteFamily;
use App\Actions\Market\ProductCategory\DeleteProductCategory;
use App\Actions\Market\ProductCategory\StoreProductCategory;
use App\Actions\Market\ProductCategory\UpdateFamily;
use App\Actions\Market\ProductCategory\UpdateProductCategory;
use App\Actions\Market\Shop\DeleteShop;
use App\Actions\Market\Shop\StoreShop;
use App\Actions\Market\Shop\UpdateShop;
use App\Actions\OMS\Order\StoreOrder;
use App\Actions\OMS\Order\UpdateOrder;
use App\Actions\Procurement\Agent\DeleteAgent;
use App\Actions\Procurement\Agent\StoreAgent;
use App\Actions\Procurement\Agent\UpdateAgent;
use App\Actions\Procurement\Marketplace\Agent\DeleteMarketplaceAgent;
use App\Actions\Procurement\Marketplace\Agent\UpdateMarketplaceAgent;
use App\Actions\Procurement\Marketplace\Supplier\UpdateMarketplaceSupplier;
use App\Actions\Procurement\PurchaseOrder\StorePurchaseOrder;
use App\Actions\Procurement\PurchaseOrder\UpdatePurchaseOrder;
use App\Actions\Procurement\Supplier\DeleteSupplier;
use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Actions\Procurement\Supplier\UpdateSupplier;
use App\Actions\Procurement\SupplierDelivery\StoreSupplierDelivery;
use App\Actions\Procurement\SupplierDelivery\UpdateSupplierDelivery;
use App\Actions\SysAdmin\Guest\DeleteGuest;
use App\Actions\SysAdmin\Guest\StoreGuest;
use App\Actions\SysAdmin\Guest\UpdateGuest;
use App\Actions\SysAdmin\Organisation\UpdateOrganisation;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Actions\UI\Profile\UpdateProfile;
use App\Actions\Web\Website\DeleteWebsite;
use App\Actions\Web\Website\StoreWebsite;
use App\Actions\Web\Website\UpdateWebsite;
use Illuminate\Support\Facades\Route;

Route::post('/shop/', StoreShop::class)->name('shop.store');

Route::patch('/shop/{shop}', UpdateShop::class)->name('shop.update');
Route::delete('/shop/{shop}', DeleteShop::class)->name('shop.delete');

Route::patch('/customer/{customer}', UpdateCustomer::class)->name('customer.update');
Route::post('/shop/{shop}/customer/', StoreCustomer::class)->name('shop.customer.store');
Route::post('/shop/{shop}/department/', [StoreProductCategory::class, 'inShop'])->name('shop.department.store');
Route::post('/shop/{shop}/website/', StoreWebsite::class)->name('shop.website.store');
Route::delete('/shop/{shop}/department/{department}', [DeleteProductCategory::class, 'inShop'])->name('shop.department.delete');


Route::post('stored-items/customer/{customer}', StoreStoredItem::class)->name('stored-items.store');
Route::patch('stored-items/{storedItem}', UpdateStoredItem::class)->name('stored-items.update');

Route::patch('/website/{website}', UpdateWebsite::class)->name('website.update');
Route::delete('/website/{website}', DeleteWebsite::class)->name('website.delete');
Route::patch('/web-user/{webUser}', UpdateWebUser::class)->name('web-user.update');



Route::post('/shop/{shop}/product/', [StoreProduct::class, 'inShop'])->name('show.product.store');
Route::post('/shop/{shop}/order/', [StoreOrder::class, 'inShop'])->name('show.order.store');

Route::post('/product/', StoreProduct::class)->name('product.store');
Route::patch('/product/{product}', UpdateProduct::class)->name('product.update');
Route::delete('/product/{product}', UpdateProduct::class)->name('product.delete');
Route::delete('/shop/{shop}/product/{product}', [DeleteProduct::class, 'inShop'])->name('shop.product.delete');

Route::patch('/department/{department}', UpdateProductCategory::class)->name('department.update');
Route::delete('/department/{department}', DeleteProductCategory::class)->name('department.delete');

Route::post('/family/', StoreProductCategory::class)->name('family.store');
Route::post('/shop/{shop}/family/', [StoreProductCategory::class, 'inShop'])->name('shop.family.store');
Route::patch('/family/{family}', UpdateFamily::class)->name('family.update');
Route::delete('/family/{family}', DeleteFamily::class)->name('family.delete');

Route::post('/order/', StoreOrder::class)->name('order.store');
Route::patch('/order/{order}', UpdateOrder::class)->name('order.update');

Route::patch('/employee/{employee}', UpdateEmployee::class)->name('employee.update');
Route::post('/employee/', StoreEmployee::class)->name('employee.store');
Route::delete('/employee/{employee}', DeleteEmployee::class)->name('employee.delete');

Route::patch('/position/{employee}', UpdateJobPosition::class)->name('job-position.update');
Route::post('/position/', StoreJobPosition::class)->name('job-position.store');
Route::delete('/position/{employee}', DeleteJobPosition::class)->name('job-position.delete');

Route::patch('/working-place/{workplace}', UpdateWorkingPlace::class)->name('working-place.update');
Route::post('/working-place/', StoreWorkingPlace::class)->name('working-place.store');
Route::delete('/working-place/{workplace}', DeleteWorkingPlace::class)->name('working-place.delete');

Route::patch('/clocking-machine/{clockingMachine}', UpdateClockingMachine::class)->name('clocking-machine.update');
Route::post('/clocking-machine', StoreClockingMachine::class)->name('clocking-machine.store');
Route::delete('/clocking-machine/{workplace}', DeleteWorkingPlace::class)->name('clocking-machine.delete');
Route::post('/working-place/{workplace}/clocking-machine', StoreClockingMachine::class)->name('working-place.clocking-machine.store');
Route::delete('/working-place/{workplace}/clocking-machine/{clockingMachine}', [ DeleteClockingMachine::class, 'inWorkplace'])->name('working-place.clocking-machine.delete');

Route::patch('/clocking/{clocking}', UpdateClocking::class)->name('clocking.update');
Route::post('/clocking', StoreClocking::class)->name('clocking.store');
Route::post('/working-place/{workplace}/clocking', StoreClocking::class)->name('working-place.clocking.store');
Route::post('/clocking-machine/{clockingMachine}/clocking', [StoreClocking::class, 'inClockingMachine'])->name('clocking-machine.clocking.store');
Route::post('/working-place/{workplace}/clocking-machine/{clockingMachine}/clocking', StoreClocking::class)->name('working-place.clocking-machine.clocking.store');
Route::delete('/working-place/{workplace}/clocking/{clocking}', [ DeleteClocking::class, 'inWorkplace'])->name('working-place.clocking.delete');
Route::delete('/clocking-machine/{clockingMachine}/clocking/{clocking}', [ DeleteClocking::class, 'inClockingMachine'])->name('clocking-machine.clocking.delete');
Route::delete('/working-place/{workplace}/clocking-machine/{clockingMachine}/clocking/{clocking}', [ DeleteClocking::class, 'inWorkplaceInClockingMachine'])->name('working-place.clocking-machine.clocking.delete');

Route::post('/warehouse/', StoreWarehouse::class)->name('warehouse.store');
Route::patch('/warehouse/{warehouse}', UpdateWarehouse::class)->name('warehouse.update');
Route::delete('/warehouse/{warehouse}', DeleteWarehouse::class)->name('warehouse.delete');

Route::post('/warehouse/{warehouse}/area/', StoreWarehouseArea::class)->name('warehouse.warehouse-area.store');
Route::post('/warehouse/{warehouse}/areas/', StoreWarehouseAreas::class)->name('warehouse.warehouse-areas.store');

Route::patch('/area/{warehouseArea}', UpdateWarehouseArea::class)->name('warehouse-area.update');
Route::delete('/area/{warehouseArea}', DeleteWarehouseArea::class)->name('warehouse-area.delete');
Route::delete('/warehouse/{warehouse}/area/{warehouseArea}', [DeleteWarehouseArea::class,'inWarehouse'])->name('warehouse.warehouse-area.delete');

Route::patch('/location/{location}', UpdateLocation::class)->name('location.update');
Route::delete('/location/{location}', DeleteLocation::class)->name('location.delete');
Route::delete('/warehouse/{warehouse}/location/{location}', [DeleteLocation::class, 'inWarehouse'])->name('warehouse.location.delete');
Route::delete('/area/{warehouseArea}/location/{location}', [DeleteLocation::class, 'inWarehouseArea'])->name('warehouse-area.location.delete');
Route::delete('/warehouse/{warehouse}/area/{warehouseArea}/location/{location}', [DeleteLocation::class, 'inWarehouseInWarehouseArea'])->name('warehouse.warehouse-area.location.delete');

Route::post('/warehouse/{warehouse}/location', StoreLocation::class)->name('warehouse.location.store');
Route::post('/area/{warehouseArea}/location', [StoreLocation::class, 'inWarehouseArea'])->name('warehouse-area.location.store');

Route::patch('/stock/{stock}', UpdateStock::class)->name('stock.update');
Route::post('/stock-family', StoreStockFamily::class)->name('stock-family.store');
Route::patch('/stock-family/{stockFamily}', UpdateStockFamily::class)->name('stock-family.update');
Route::delete('/stock-family/{stockFamily}', DeleteStockFamily::class)->name('stock-family.delete');
Route::post('/stock-family/{stockFamily}/stock', [StoreStock::class,'inStockFamily'])->name('stock-family.stock.store');
Route::patch('/stock-family/{stockFamily}/stock/{stock}', [UpdateStock::class,'inStockFamily'])->name('stock-family.stock.update');
Route::delete('/stock-family/{stockFamily}/stock/{stock}', [DeleteStock::class, 'inStockFamily'])->name('stock-family.stock.delete');

Route::patch('/agent/{agent}', UpdateAgent::class)->name('agent.update');
Route::post('/agent/{agent}/purchase-order', [StorePurchaseOrder::class, 'inAgent'])->name('agent.purchase-order.store');
Route::delete('/agent/{agent}', DeleteAgent::class)->name('agent.delete');

Route::post('/agent/', StoreAgent::class)->name('agent.store');

Route::patch('/supplier/{supplier}', UpdateSupplier::class)->name('supplier.update');
Route::delete('/supplier/{supplier}', DeleteSupplier::class)->name('supplier.delete');
Route::post('/supplier/', StoreSupplier::class)->name('supplier.store');

Route::post('/agent/{agent}/supplier', [StoreSupplier::class, 'inAgent'])->name('agent.supplier.store');
Route::post('/agent/{supplier}/purchase-order', [StorePurchaseOrder::class, 'inSupplier'])->name('supplier.purchase-order.store');
Route::post('/supplier/{supplier}/purchase-order', [StorePurchaseOrder::class, 'inSupplier'])->name('supplier.purchase-order.store');


Route::post('/provider', StorePaymentServiceProvider::class)->name('payment-service-provider.store');
Route::patch('/provider/{paymentServiceProvider}', UpdatePaymentServiceProvider::class)->name('payment-service-provider.update');
Route::delete('/provider/{paymentServiceProvider}', DeletePaymentServiceProvider::class)->name('payment-service-provider.delete');

Route::patch('/payment/{payment}', UpdatePayment::class)->name('payment.update');

Route::patch('/payment-account/{paymentAccount}', UpdatePaymentAccount::class)->name('payment-account.update');
Route::post('/payment-account', StorePaymentAccount::class)->name('payment-account.store');

Route::patch('/user/{user}', UpdateUser::class)->name('user.update');
Route::patch('/profile', UpdateProfile::class)->name('profile.update');


Route::patch('/guest/{guest}', UpdateGuest::class)->name('guest.update');
Route::post('/guest/', StoreGuest::class)->name('guest.store');
Route::delete('/guest/{guest}', DeleteGuest::class)->name('guest.delete');
Route::post('/group-user/{GroupUser}guest/', [StoreGuest::class, 'inGroupUser'])->name('group-user.guest.store');

Route::patch('/outbox/{outbox}', UpdateOutbox::class)->name('outbox.update');

Route::patch('/purchase-order/{purchaseOrder}', UpdatePurchaseOrder::class)->name('purchase-order.update');

Route::patch('/supplier-delivery/{supplierDelivery}', UpdateSupplierDelivery::class)->name('supplier-delivery.update');
Route::post('/supplier-delivery/', StoreSupplierDelivery::class)->name('supplier-delivery.store');
Route::patch('/marketplace-agent/{marketplaceAgent}', UpdateMarketplaceAgent::class)->name('marketplace-agent.update');
Route::delete('/marketplace-agent/{marketplaceAgent}', DeleteMarketplaceAgent::class)->name('marketplace-agent.delete');

Route::patch('/marketplace-supplier/{marketplaceSupplier}', UpdateMarketplaceSupplier::class)->name('marketplace-supplier.update');

Route::patch('/system-settings', UpdateOrganisation::class)->name('system-settings.update');
