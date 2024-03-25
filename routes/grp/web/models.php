<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 10:25:11 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */


use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\Prospect\ImportShopProspects;
use App\Actions\Fulfilment\Fulfilment\StoreFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\UpdateFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\DeletePallet;
use App\Actions\Fulfilment\Pallet\ImportPallet;
use App\Actions\Fulfilment\Pallet\StoreMultiplePallets;
use App\Actions\Fulfilment\Pallet\StorePalletFromDelivery;
use App\Actions\Fulfilment\Pallet\StorePalletToReturn;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\Pallet\UpdatePalletBookedIn;
use App\Actions\Fulfilment\Pallet\UpdatePalletNotReceived;
use App\Actions\Fulfilment\PalletDelivery\ConfirmPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\BookInPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\DeletePalletFromDelivery;
use App\Actions\Fulfilment\PalletDelivery\ReceivedPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\SubmitPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\UpdatePalletDeliveryTimeline;
use App\Actions\Fulfilment\PalletReturn\ConfirmPalletReturn;
use App\Actions\Fulfilment\PalletReturn\DeletePalletFromReturn;
use App\Actions\Fulfilment\PalletReturn\DispatchedPalletReturn;
use App\Actions\Fulfilment\PalletReturn\PickingPalletReturn;
use App\Actions\Fulfilment\PalletReturn\PickedPalletReturn;
use App\Actions\Fulfilment\PalletReturn\StorePalletReturn;
use App\Actions\Fulfilment\PalletReturn\SubmitPalletReturn;
use App\Actions\Fulfilment\StoredItem\MoveStoredItem;
use App\Actions\Fulfilment\StoredItem\StoreStoredItem;
use App\Actions\Fulfilment\StoredItem\SyncStoredItemToPallet;
use App\Actions\Fulfilment\StoredItemReturn\DeleteStoredItemFromStoredItemReturn;
use App\Actions\Fulfilment\StoredItemReturn\StoreStoredItemReturn;
use App\Actions\Fulfilment\StoredItemReturn\StoreStoredItemToStoredItemReturn;
use App\Actions\Fulfilment\StoredItemReturn\UpdateStateStoredItemReturn;
use App\Actions\Helpers\Tag\StoreTag;
use App\Actions\HumanResources\Employee\DeleteEmployee;
use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Actions\HumanResources\Workplace\DeleteWorkplace;
use App\Actions\HumanResources\Workplace\StoreWorkplace;
use App\Actions\HumanResources\Workplace\UpdateWorkplace;
use App\Actions\Inventory\Location\ImportLocation;
use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Inventory\Location\Tags\SyncTagsLocation;
use App\Actions\Inventory\Location\UpdateLocation;
use App\Actions\Inventory\Warehouse\UpdateWarehouse;
use App\Actions\Inventory\WarehouseArea\ImportWarehouseArea;
use App\Actions\Market\Shop\StoreShop;
use App\Actions\SysAdmin\Organisation\StoreOrganisation;
use App\Actions\UI\Profile\GetProfileAppLoginQRCode;
use App\Actions\UI\Profile\UpdateProfile;
use App\Actions\Web\Website\LaunchWebsite;
use App\Actions\Web\Website\StoreWebsite;
use App\Actions\Web\Website\UpdateWebsite;
use App\Enums\Fulfilment\StoredItemReturn\StoredItemReturnStateEnum;
use Illuminate\Support\Facades\Route;

Route::patch('/profile', UpdateProfile::class)->name('profile.update');
Route::get('/profile/app-login-qrcode', GetProfileAppLoginQRCode::class)->name('profile.app-login-qrcode');


Route::patch('/employees/{employee:id}', UpdateEmployee::class)->name('employee.update');
Route::delete('/employee/{employee}', DeleteEmployee::class)->name('employee.delete');
Route::patch('/working-place/{workplace}', UpdateWorkplace::class)->name('working-place.update');
Route::delete('/working-place/{workplace}', DeleteWorkplace::class)->name('working-place.delete');

Route::name('org.')->prefix('org/{organisation}')->group(function () {
    Route::post('/employee/', StoreEmployee::class)->name('employee.store');
    Route::post('/working-place/', StoreWorkplace::class)->name('working-place.store');
    Route::post('/shop/', StoreShop::class)->name('shop.store');
    Route::post('/fulfilment/', StoreFulfilment::class)->name('fulfilment.store');

    Route::post('/shop/{shop}/customer/', StoreCustomer::class)->name('shop.customer.store');
});

Route::name('pallet-delivery.')->prefix('pallet-delivery/{palletDelivery:id}')->group(function () {

    Route::post('submit', SubmitPalletDelivery::class)->name('submit');
    Route::post('confirm', ConfirmPalletDelivery::class)->name('confirm');
    Route::post('received', ReceivedPalletDelivery::class)->name('received');
    Route::post('done', BookInPalletDelivery::class)->name('done');


    Route::post('pallet-upload', [ImportPallet::class,'fromGrp'])->name('pallet.import');
    Route::post('pallet', StorePalletFromDelivery::class)->name('pallet.store');
    Route::post('multiple-pallet', StoreMultiplePallets::class)->name('multiple-pallets.store');


});

Route::name('pallet.')->prefix('pallet/{pallet:id}')->group(function () {
    Route::delete('', DeletePallet::class)->name('delete');
    Route::patch('', UpdatePallet::class)->name('update');

    Route::post('stored-items', SyncStoredItemToPallet::class)->name('stored-items.update');
});

Route::patch('{storedItem}/stored-items', MoveStoredItem::class)->name('stored-items.move');

Route::name('fulfilment-customer.')->prefix('fulfilment-customer/{fulfilmentCustomer:id}')->group(function () {
    Route::post('stored-item-return', StoreStoredItemReturn::class)->name('stored-item-return.store');
    Route::post('stored-items', StoreStoredItem::class)->name('stored-items.store');

    Route::patch('', UpdateFulfilmentCustomer::class)->name('update');
    Route::post('pallet-delivery', StorePalletDelivery::class)->name('pallet-delivery.store');
    Route::delete('pallet-delivery/{palletDelivery}/pallet/{pallet}', DeletePalletFromDelivery::class)->name('pallet-delivery.pallet.delete');


    Route::patch('pallet-delivery/{palletDelivery}/timeline', UpdatePalletDeliveryTimeline::class)->name('pallet-delivery.timeline.update');


    Route::post('pallet-return', StorePalletReturn::class)->name('pallet-return.store');

    Route::prefix('pallet-return/{palletReturn}')->name('pallet-return.')->group(function () {
        Route::delete('pallet/{pallet}', DeletePalletFromReturn::class)->name('pallet.delete');
        Route::post('pallet', StorePalletToReturn::class)->name('pallet.store');
        Route::post('submit', SubmitPalletReturn::class)->name('submit');
        Route::post('delivery', PickingPalletReturn::class)->name('picking');
        Route::post('confirm', ConfirmPalletReturn::class)->name('confirm');
        Route::post('received', PickedPalletReturn::class)->name('picked');
        Route::post('dispatched', DispatchedPalletReturn::class)->name('dispatched');
    });

    Route::prefix('stored-item-return/{storedItemReturn}')->name('stored-item-return.')->group(function () {
        Route::delete('stored-item/{storedItem}', DeleteStoredItemFromStoredItemReturn::class)->name('stored-item.delete');
        Route::post('stored-item', StoreStoredItemToStoredItemReturn::class)->name('stored-item.store');
        Route::post('state/{state}', UpdateStateStoredItemReturn::class)->name('state.update')->whereIn('state', StoredItemReturnStateEnum::values());
    });
});

Route::name('shop.')->prefix('shop/{shop:id}')->group(function () {
    Route::post('prospect/upload', [ImportShopProspects::class, 'inShop'])->name('prospects.upload');
    Route::post('website', StoreWebsite::class)->name('website.store');
});

Route::name('fulfilment.')->prefix('fulfilment/{fulfilment:id}')->group(function () {
    Route::post('website', [StoreWebsite::class,'inFulfilment'])->name('website.store');
});

Route::name('warehouse.')->prefix('warehouse/{warehouse:id}')->group(function () {
    Route::patch('/', UpdateWarehouse::class)->name('warehouse.update');
    Route::post('areas/upload', [ImportWarehouseArea::class, 'inWarehouse'])->name('warehouse-areas.upload');

    Route::post('location/upload', [ImportLocation::class, 'inWarehouse'])->name('location.upload');
    Route::post('location', [StoreLocation::class, 'inWarehouse'])->name('location.store');
    Route::patch('pallet/{pallet:id}/booked-in', UpdatePalletBookedIn::class)->name('pallet.booked-in')->withoutScopedBindings();
    Route::patch('pallet/{pallet:id}/not-received', UpdatePalletNotReceived::class)->name('pallet.not-received')->withoutScopedBindings();
    Route::patch('pallet/{pallet:id}/undo-not-received', [UpdatePalletNotReceived::class, 'undo'])->name('pallet.undo-not-received')->withoutScopedBindings();

});

Route::patch('location/{location:id}', UpdateLocation::class)->name('location.update');

Route::patch('location/{location:id}/tags', SyncTagsLocation::class)->name('location.tag.attach');
Route::post('location/{location:id}/tags', [StoreTag::class, 'inLocation'])->name('location.tag.store');

Route::name('warehouse-area.')->prefix('warehouse-area/{warehouseArea:id}')->group(function () {
    Route::post('location/upload', [ImportLocation::class, 'inWarehouseArea'])->name('location.upload');
    Route::post('location', [StoreLocation::class, 'inWarehouseArea'])->name('location.store');
});

Route::post('group/{group:id}/organisation', StoreOrganisation::class)->name('organisation.store');


Route::name('website.')->prefix('website/{website:id}')->group(function () {
    Route::patch('', UpdateWebsite::class)->name('update');
    Route::post('launch', LaunchWebsite::class)->name('launch');
});

/*



Route::patch('/shop/{shop}', UpdateShop::class)->name('shop.update');
Route::delete('/shop/{shop}', DeleteShop::class)->name('shop.delete');

Route::patch('/customer/{customer}', UpdateCustomer::class)->name('customer.update');
Route::post('/shop/{shop}/customer/', StoreCustomer::class)->name('shop.customer.store');
Route::post('/shop/{shop}/department/', [StoreProductCategory::class, 'inShop'])->name('shop.department.store');
Route::post('/shop/{shop}/website/', StoreWebsite::class)->name('shop.website.store');
Route::delete('/shop/{shop}/department/{department}', [DeleteProductCategory::class, 'inShop'])->name('shop.department.delete');


Route::post('stored-items/customer/{customer}', StoreStoredItem::class)->name('stored-items.store');
Route::patch('stored-items/{storedItem}', UpdateStoredItem::class)->name('stored-items.update');

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


Route::patch('/position/{employee}', UpdateJobPosition::class)->name('job-position.update');
Route::post('/position/', StoreJobPosition::class)->name('job-position.store');
Route::delete('/position/{employee}', DeleteJobPosition::class)->name('job-position.delete');


Route::patch('/clocking-machine/{clockingMachine}', UpdateClockingMachine::class)->name('clocking-machine.update');
Route::post('/clocking-machine', StoreClockingMachine::class)->name('clocking-machine.store');
Route::delete('/clocking-machine/{workplace}', DeleteWorkplace::class)->name('clocking-machine.delete');
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
*/
