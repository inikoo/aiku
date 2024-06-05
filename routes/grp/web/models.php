<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 10:25:11 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */


use App\Actions\Accounting\OrgPaymentServiceProvider\StoreOrgPaymentServiceProvider;
use App\Actions\Accounting\OrgPaymentServiceProvider\StoreOrgPaymentServiceProviderAccount;
use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\Accounting\PaymentAccount\UpdatePaymentAccount;
use App\Actions\Catalogue\Collection\StoreCollection;
use App\Actions\Catalogue\Collection\UpdateCollection;
use App\Actions\Catalogue\Product\DeleteProduct;
use App\Actions\Catalogue\Product\StoreProduct;
use App\Actions\Catalogue\Product\UpdateProduct;
use App\Actions\Catalogue\ProductCategory\StoreProductCategory;
use App\Actions\Catalogue\ProductCategory\UpdateProductCategory;
use App\Actions\Catalogue\Service\StoreService;
use App\Actions\Catalogue\Service\UpdateService;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\SyncPaymentAccountToShop;
use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\Customer\UpdateCustomer;
use App\Actions\CRM\Prospect\ImportShopProspects;
use App\Actions\CRM\WebUser\StoreWebUser;
use App\Actions\CRM\WebUser\UpdateWebUser;
use App\Actions\Devel\UI\ImportDummy;
use App\Actions\Fulfilment\Fulfilment\StoreFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\StoreFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\UpdateFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\BookInPallet;
use App\Actions\Fulfilment\Pallet\DeletePallet;
use App\Actions\Fulfilment\Pallet\ImportPallet;
use App\Actions\Fulfilment\Pallet\SetPalletAsDamaged;
use App\Actions\Fulfilment\Pallet\SetPalletAsLost;
use App\Actions\Fulfilment\Pallet\SetPalletAsNotReceived;
use App\Actions\Fulfilment\Pallet\SetPalletRental;
use App\Actions\Fulfilment\Pallet\StoreMultiplePalletsFromDelivery;
use App\Actions\Fulfilment\Pallet\StorePalletFromDelivery;
use App\Actions\Fulfilment\Pallet\StorePalletToReturn;
use App\Actions\Fulfilment\Pallet\UndoPalletStateToReceived;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\Pallet\UpdatePalletLocation;
use App\Actions\Fulfilment\PalletDelivery\ConfirmPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\DeletePalletInDelivery;
use App\Actions\Fulfilment\PalletDelivery\PdfPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\ReceivedPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\SetPalletDeliveryAsBookedIn;
use App\Actions\Fulfilment\PalletDelivery\StartBookingPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\SubmitPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\UpdatePalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\UpdatePalletDeliveryTimeline;
use App\Actions\Fulfilment\PalletDeliveryPhysicalGood\SyncPhysicalGoodToPalletDelivery;
use App\Actions\Fulfilment\PalletDeliveryService\SyncServiceToPalletDelivery;
use App\Actions\Fulfilment\PalletReturn\ConfirmPalletReturn;
use App\Actions\Fulfilment\PalletReturn\DeletePalletFromReturn;
use App\Actions\Fulfilment\PalletReturn\DispatchedPalletReturn;
use App\Actions\Fulfilment\PalletReturn\PdfPalletReturn;
use App\Actions\Fulfilment\PalletReturn\PickedPalletReturn;
use App\Actions\Fulfilment\PalletReturn\PickingPalletReturn;
use App\Actions\Fulfilment\PalletReturn\StorePalletReturn;
use App\Actions\Fulfilment\PalletReturn\SubmitPalletReturn;
use App\Actions\Fulfilment\PalletReturn\UpdatePalletReturn;
use App\Actions\Fulfilment\PalletReturnItem\UpdatePalletReturnItem;
use App\Actions\Fulfilment\PalletReturnPhysicalGood\SyncPhysicalGoodToPalletReturn;
use App\Actions\Fulfilment\PalletReturnService\SyncServiceToPalletReturn;
use App\Actions\Fulfilment\Rental\StoreRental;
use App\Actions\Fulfilment\Rental\UpdateRental;
use App\Actions\Fulfilment\RentalAgreement\StoreRentalAgreement;
use App\Actions\Fulfilment\RentalAgreement\UpdateRentalAgreement;
use App\Actions\Fulfilment\StoredItem\MoveStoredItem;
use App\Actions\Fulfilment\StoredItem\StoreStoredItem;
use App\Actions\Fulfilment\StoredItem\SyncStoredItemToPallet;
use App\Actions\Fulfilment\StoredItemReturn\DeleteStoredItemFromStoredItemReturn;
use App\Actions\Fulfilment\StoredItemReturn\StoreStoredItemReturn;
use App\Actions\Fulfilment\StoredItemReturn\StoreStoredItemToStoredItemReturn;
use App\Actions\Fulfilment\StoredItemReturn\UpdateStateStoredItemReturn;
use App\Actions\Helpers\Tag\StoreTag;
use App\Actions\HumanResources\ClockingMachine\DeleteClockingMachine;
use App\Actions\HumanResources\ClockingMachine\StoreClockingMachine;
use App\Actions\HumanResources\ClockingMachine\UpdateClockingMachine;
use App\Actions\HumanResources\Employee\DeleteEmployee;
use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Actions\HumanResources\JobPosition\DeleteJobPosition;
use App\Actions\HumanResources\JobPosition\StoreJobPosition;
use App\Actions\HumanResources\JobPosition\UpdateJobPosition;
use App\Actions\HumanResources\Workplace\DeleteWorkplace;
use App\Actions\HumanResources\Workplace\StoreWorkplace;
use App\Actions\HumanResources\Workplace\UpdateWorkplace;
use App\Actions\Inventory\Location\ImportLocation;
use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Inventory\Location\Tags\SyncTagsLocation;
use App\Actions\Inventory\Location\UpdateLocation;
use App\Actions\Inventory\Warehouse\UpdateWarehouse;
use App\Actions\Inventory\WarehouseArea\ImportWarehouseArea;
use App\Actions\Manufacturing\Artefact\ImportArtefact;
use App\Actions\Manufacturing\Artefact\StoreArtefact;
use App\Actions\Manufacturing\Artefact\UpdateArtefact;
use App\Actions\Manufacturing\JobOrder\StoreJobOrder;
use App\Actions\Manufacturing\JobOrder\UpdateJobOrder;
use App\Actions\Manufacturing\ManufactureTask\StoreManufactureTask;
use App\Actions\Manufacturing\ManufactureTask\UpdateManufactureTask;
use App\Actions\Manufacturing\RawMaterial\ImportRawMaterial;
use App\Actions\Manufacturing\RawMaterial\StoreRawMaterial;
use App\Actions\Manufacturing\RawMaterial\UpdateRawMaterial;
use App\Actions\SupplyChain\Agent\StoreAgent;
use App\Actions\SupplyChain\Supplier\StoreSupplier;
use App\Actions\SysAdmin\Organisation\StoreOrganisation;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Actions\UI\Notification\MarkAllNotificationAsRead;
use App\Actions\UI\Notification\MarkNotificationAsRead;
use App\Actions\UI\Profile\GetProfileAppLoginQRCode;
use App\Actions\UI\Profile\UpdateProfile;
use App\Actions\Web\Webpage\UpdateWebpage;
use App\Actions\Web\Webpage\UpdateWebpageContent;
use App\Actions\Web\Website\LaunchWebsite;
use App\Actions\Web\Website\StoreWebsite;
use App\Actions\Web\Website\UpdateWebsite;
use App\Enums\Fulfilment\StoredItemReturn\StoredItemReturnStateEnum;
use Illuminate\Support\Facades\Route;

Route::patch('/profile', UpdateProfile::class)->name('profile.update');
Route::get('/profile/app-login-qrcode', GetProfileAppLoginQRCode::class)->name('profile.app-login-qrcode');

Route::patch('/user/{user:id}', UpdateUser::class)->name('user.update');
Route::patch('notification/{notification}', MarkNotificationAsRead::class)->name('notifications.read');
Route::patch('notifications', MarkAllNotificationAsRead::class)->name('notifications.all.read');

Route::post('/agent/', StoreAgent::class)->name('agent.store');


Route::prefix('employee/{employee:id}')->name('employee.')->group(function () {
    Route::patch('', UpdateEmployee::class)->name('update');
    Route::delete('', DeleteEmployee::class)->name('.delete');
});

Route::prefix('workplace/{workplace:id}')->name('workplace.')->group(function () {
    Route::patch('', UpdateWorkplace::class)->name('update');
    Route::delete('', DeleteWorkplace::class)->name('delete');
    Route::post('clocking-machine', StoreClockingMachine::class)->name('clocking_machine.store');
});


Route::prefix('position/{workplace:id}')->name('jon_position.')->group(function () {
    Route::patch('', UpdateJobPosition::class)->name('update');
    Route::delete('', DeleteJobPosition::class)->name('delete');
});

Route::prefix('clocking-machine/{clockingMachine:id}')->name('clocking_machine..')->group(function () {
    Route::patch('', UpdateClockingMachine::class)->name('update');
    Route::delete('', DeleteClockingMachine::class)->name('delete');
});



/*

Route::patch('/clocking/{clocking:id}', UpdateClocking::class)->name('clocking.update');
Route::post('/clocking', StoreClocking::class)->name('clocking.store');
Route::post('/working-place/{workplace:id}/clocking', StoreClocking::class)->name('workplace.clocking.store');
Route::post('/clocking-machine/{clockingMachine:id}/clocking', [StoreClocking::class, 'inClockingMachine'])->name('clocking-machine.clocking.store');
Route::post('/working-place/{workplace:id}/clocking-machine/{clockingMachine:id}/clocking', StoreClocking::class)->name('workplace.clocking-machine.clocking.store');
Route::delete('/working-place/{workplace:id}/clocking/{clocking:id}', [ DeleteClocking::class, 'inWorkplace'])->name('workplace.clocking.delete');
Route::delete('/clocking-machine/{clockingMachine:id}/clocking/{clocking:id}', [ DeleteClocking::class, 'inClockingMachine'])->name('clocking-machine.clocking.delete');
Route::delete('/working-place/{workplace:id}/clocking-machine/{clockingMachine:id}/clocking/{clocking:id}', [ DeleteClocking::class, 'inWorkplaceInClockingMachine'])->name('workplace.clocking-machine.clocking.delete');
*/

Route::name('org.')->prefix('org/{organisation:id}')->group(function () {

    Route::post('employee', StoreEmployee::class)->name('employee.store');
    Route::post('position', StoreJobPosition::class)->name('jon_position.store');
    Route::post('working-place', StoreWorkplace::class)->name('workplace.store');
    Route::post('clocking-machine', [StoreClockingMachine::class, 'inOrganisation'])->name('clocking-machine.store');


    Route::post('shop', StoreShop::class)->name('shop.store');
    Route::post('fulfilment', StoreFulfilment::class)->name('fulfilment.store');


    Route::prefix('fulfilment/{fulfilment:id}/rentals')->name('fulfilment.rentals.')->group(function () {
        Route::post('/', StoreRental::class)->name('store');
        Route::patch('{rental:id}', UpdateRental::class)->name('update')->withoutScopedBindings();
    });
    Route::prefix('fulfilment/{fulfilment:id}/services')->name('fulfilment.services.')->group(function () {
        Route::post('/', [StoreService::class, 'inFulfilment'])->name('store');
        Route::patch('{service:id}', UpdateService::class)->name('update')->withoutScopedBindings();
    });

    Route::prefix('/shop/{shop:id}/catalogue/collections')->name('catalogue.collections.')->group(function () {
        Route::post('/', StoreCollection::class)->name('store');
        Route::patch('{collection:id}', UpdateCollection::class)->name('update')->withoutScopedBindings();
    });

    Route::prefix('/shop/{shop:id}/catalogue/departments')->name('catalogue.departments.')->group(function () {
        // Route::post('/', Store::class)->name('store');
        Route::patch('{productCategory:id}', UpdateProductCategory::class)->name('update')->withoutScopedBindings();
        Route::post('family/store/{productCategory:id}', [StoreProductCategory::class, 'inDepartment'])->name('family.store')->withoutScopedBindings();
    });

    Route::post('/shop/{shop:id}/customer', StoreCustomer::class)->name('shop.customer.store');
    Route::patch('/shop/{shop:id}/customer/{customer:id}', UpdateCustomer::class)->name('shop.customer.update')->withoutScopedBindings();
    Route::post('/shop/{shop:id}/fulfilment/{fulfilment:id}/customer', StoreFulfilmentCustomer::class)->name('shop.fulfilment-customer.store')->withoutScopedBindings();

    Route::post('/shop/{shop:id}/product/', [StoreProduct::class, 'inShop'])->name('show.product.store');
    Route::delete('/shop/{shop:id}/product/{product:id}', [DeleteProduct::class, 'inShop'])->name('shop.product.delete');

    Route::post('/product/', StoreProduct::class)->name('product.store');
    Route::patch('/product/{product:id}', UpdateProduct::class)->name('product.update');
    Route::delete('/product/{product:id}', UpdateProduct::class)->name('product.delete');

    Route::patch('/payment-account/{paymentAccount:id}', UpdatePaymentAccount::class)->name('payment-account.update')->withoutScopedBindings();
    Route::post('/payment-account', StorePaymentAccount::class)->name('payment-account.store');
    Route::post('/payment-service-provider/{paymentServiceProvider:id}', StoreOrgPaymentServiceProvider::class)->name('payment-service-provider.store')->withoutScopedBindings();

    Route::post('/payment-service-provider/{paymentServiceProvider:id}/account', StoreOrgPaymentServiceProviderAccount::class)->name('payment-service-provider-account.store')->withoutScopedBindings();
});

Route::name('pallet-delivery.')->prefix('pallet-delivery/{palletDelivery:id}')->group(function () {
    Route::patch('/', UpdatePalletDelivery::class)->name('update');
    Route::post('submit', SubmitPalletDelivery::class)->name('submit');
    Route::post('confirm', ConfirmPalletDelivery::class)->name('confirm');
    Route::post('received', ReceivedPalletDelivery::class)->name('received');
    Route::post('booking', StartBookingPalletDelivery::class)->name('booking');
    Route::post('booked-in', SetPalletDeliveryAsBookedIn::class)->name('booked-in');


    Route::post('pallet-upload', [ImportPallet::class, 'fromGrp'])->name('pallet.upload');
    Route::post('pallet', StorePalletFromDelivery::class)->name('pallet.store');
    Route::post('multiple-pallet', StoreMultiplePalletsFromDelivery::class)->name('multiple-pallets.store');

    Route::post('service', SyncServiceToPalletDelivery::class)->name('service.store');
    Route::post('physical-goods', SyncPhysicalGoodToPalletDelivery::class)->name('physical_good.store');

    Route::get('pdf', PdfPalletDelivery::class)->name('pdf');
});

Route::name('pallet-return.')->prefix('pallet-return/{palletReturn:id}')->group(function () {
    Route::post('service', SyncServiceToPalletReturn::class)->name('service.store');
    Route::post('physical-goods', SyncPhysicalGoodToPalletReturn::class)->name('physical_good.store');

    Route::patch('/', UpdatePalletReturn::class)->name('update');
    Route::get('pdf', PdfPalletReturn::class)->name('pdf');
});

Route::name('pallet.')->prefix('pallet/{pallet:id}')->group(function () {
    Route::delete('', DeletePallet::class)->name('delete');
    Route::patch('', UpdatePallet::class)->name('update');
    Route::patch('rental', SetPalletRental::class)->name('rental.update');

    Route::patch('pallet-return-item', UpdatePalletReturnItem::class)->name('pallet-return-item.update');

    Route::post('stored-items', SyncStoredItemToPallet::class)->name('stored-items.update');
    Route::patch('booked-in', BookInPallet::class)->name('booked-in');
    Route::patch('not-received', SetPalletAsNotReceived::class)->name('not-received');
    Route::patch('undo-not-received', UndoPalletStateToReceived::class)->name('undo-not-received');
    Route::patch('undo-booked-in', UndoPalletStateToReceived::class)->name('undo-booked-in');

    Route::patch('damaged', SetPalletAsDamaged::class)->name('damaged');
    Route::patch('lost', SetPalletAsLost::class)->name('lost');
});

Route::patch('{storedItem:id}/stored-items', MoveStoredItem::class)->name('stored-items.move');

Route::name('fulfilment-customer.')->prefix('fulfilment-customer/{fulfilmentCustomer:id}')->group(function () {
    Route::patch('', UpdateFulfilmentCustomer::class)->name('update')->withoutScopedBindings();

    Route::post('stored-item-return', StoreStoredItemReturn::class)->name('stored-item-return.store');
    Route::post('stored-items', StoreStoredItem::class)->name('stored-items.store');
    Route::patch('', UpdateFulfilmentCustomer::class)->name('update');
    Route::post('pallet-delivery', StorePalletDelivery::class)->name('pallet-delivery.store');
    Route::delete('pallet-delivery/{palletDelivery:id}/pallet/{pallet:id}', DeletePalletInDelivery::class)->name('pallet-delivery.pallet.delete');
    Route::get('pallet-delivery/{palletDelivery:id}/export', PdfPalletDelivery::class)->name('pallet-delivery.export');
    Route::patch('pallet-delivery/{palletDelivery:id}/timeline', UpdatePalletDeliveryTimeline::class)->name('pallet-delivery.timeline.update');
    Route::post('pallet-return', StorePalletReturn::class)->name('pallet-return.store');
    Route::post('', [StoreWebUser::class, 'inFulfilmentCustomer'])->name('web-user.store');

    Route::prefix('pallet-return/{palletReturn:id}')->name('pallet-return.')->group(function () {
        Route::delete('pallet/{pallet:id}', DeletePalletFromReturn::class)->name('pallet.delete');
        Route::post('pallet', StorePalletToReturn::class)->name('pallet.store');
        Route::post('submit', SubmitPalletReturn::class)->name('submit');
        Route::post('delivery', PickingPalletReturn::class)->name('picking');
        Route::post('confirm', ConfirmPalletReturn::class)->name('confirm');
        Route::post('received', PickedPalletReturn::class)->name('picked');
        Route::post('dispatched', DispatchedPalletReturn::class)->name('dispatched');
    });

    Route::prefix('stored-item-return/{storedItemReturn:id}')->name('stored-item-return.')->group(function () {
        Route::delete('stored-item/{storedItem:id}', DeleteStoredItemFromStoredItemReturn::class)->name('stored-item.delete');
        Route::post('stored-item', StoreStoredItemToStoredItemReturn::class)->name('stored-item.store');
        Route::post('state/{state}', UpdateStateStoredItemReturn::class)->name('state.update')->whereIn('state', StoredItemReturnStateEnum::values());
    });

    Route::prefix('rental-agreements')->name('rental-agreements.')->group(function () {
        Route::post('/', StoreRentalAgreement::class)->name('store');
        Route::patch('{rentalAgreement:id}', UpdateRentalAgreement::class)->name('update')->withoutScopedBindings();
    });
});

Route::name('shop.')->prefix('shop/{shop:id}')->group(function () {
    Route::post('prospect/upload', [ImportShopProspects::class, 'inShop'])->name('prospects.upload');
    Route::post('website', StoreWebsite::class)->name('website.store');

    Route::name('webpage.')->prefix('webpage/{webpage:id}')->group(function () {
        Route::patch('', [UpdateWebpage::class, 'inShop'])->name('update')->withoutScopedBindings();
    });
});

Route::name('fulfilment.')->prefix('fulfilment/{fulfilment:id}')->group(function () {
    Route::post('website', [StoreWebsite::class, 'inFulfilment'])->name('website.store');
});

Route::name('warehouse.')->prefix('warehouse/{warehouse:id}')->group(function () {
    Route::patch('/', UpdateWarehouse::class)->name('warehouse.update');
    Route::post('areas/upload', [ImportWarehouseArea::class, 'inWarehouse'])->name('warehouse-areas.upload');

    Route::patch('pallet/{pallet:id}/locations', [UpdatePalletLocation::class, 'inWarehouse'])->name('pallets.location.update')->withoutScopedBindings();

    Route::post('location/upload', [ImportLocation::class, 'inWarehouse'])->name('location.upload');
    Route::post('location', [StoreLocation::class, 'inWarehouse'])->name('location.store');
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

Route::patch('/web-user/{webUser:id}', UpdateWebUser::class)->name('web-user.update');

Route::name('customer.')->prefix('customer/{customer:id}')->group(function () {
    Route::post('', [StoreWebUser::class, 'inCustomer'])->name('web-user.store');
});

Route::post('/supplier', StoreSupplier::class)->name('supplier.store');
Route::patch('/shop/payment-accounts/{paymentAccount:id}', SyncPaymentAccountToShop::class)->name('shop.payment-accounts.sync')->withoutScopedBindings();

Route::name('production.')->prefix('production/{production:id}')->group(function () {
    Route::post('job-order', StoreJobOrder::class)->name('job-order.store');
    Route::post('artefact-upload', ImportDummy::class)->name('artefacts.upload');
    Route::post('raw-materials-upload', ImportRawMaterial::class)->name('raw_materials.upload');
    Route::post('manufacture-tasks-upload', ImportDummy::class)->name('manufacture_tasks.upload');
    Route::post('raw-materials', StoreRawMaterial::class)->name('raw-materials.store');
    Route::patch('raw-materials/{rawMaterial:id}', UpdateRawMaterial::class)->name('raw-materials.update');
    Route::post('manufacture-tasks', StoreManufactureTask::class)->name('manufacture_tasks.store');
    Route::patch('manufacture-tasks/{manufactureTask:id}', UpdateManufactureTask::class)->name('manufacture_tasks.update');
    Route::post('artefacts', StoreArtefact::class)->name('artefacts.store');
    Route::patch('artefacts/{artefact:id}', UpdateArtefact::class)->name('artefacts.update');
    Route::post('artefact-upload', ImportArtefact::class)->name('artefact.import');
});

Route::patch('/job-order/{jobOrder:id}', UpdateJobOrder::class)->name('job-order.update');


Route::name('webpage.')->prefix('webpage/{webpage:id}')->group(function () {

    Route::name('content.')->prefix('content')->group(function () {
        Route::patch('/', UpdateWebpageContent::class)->name('update');
    });
});



/*




Route::delete('/shop/{shop:id}', DeleteShop::class)->name('shop.delete');

Route::post('/shop/{shop:id}/customer/', StoreCustomer::class)->name('shop.customer.store');
Route::post('/shop/{shop:id}/department/', [StoreProductCategory::class, 'inShop'])->name('shop.department.store');
Route::post('/shop/{shop:id}/website/', StoreWebsite::class)->name('shop.website.store');
Route::delete('/shop/{shop:id}/department/{department:id}', [DeleteProductCategory::class, 'inShop'])->name('shop.department.delete');


Route::post('stored-items/customer/{customer:id}', StoreStoredItem::class)->name('stored-items.store');
Route::patch('stored-items/{storedItem:id}', UpdateStoredItem::class)->name('stored-items.update');

Route::delete('/website/{website:id}', DeleteWebsite::class)->name('website.delete');



Route::post('/shop/{shop:id}/product/', [StoreProduct::class, 'inShop'])->name('show.product.store');
Route::post('/shop/{shop:id}/order/', [StoreOrder::class, 'inShop'])->name('show.order.store');

Route::post('/product/', StoreProduct::class)->name('product.store');
Route::patch('/product/{product:id}', UpdateProduct::class)->name('product.update');
Route::delete('/product/{product:id}', UpdateProduct::class)->name('product.delete');
Route::delete('/shop/{shop:id}/product/{product:id}', [DeleteProduct::class, 'inShop'])->name('shop.product.delete');

Route::patch('/department/{department:id}', UpdateProductCategory::class)->name('department.update');
Route::delete('/department/{department:id}', DeleteProductCategory::class)->name('department.delete');

Route::post('/family/', StoreProductCategory::class)->name('family.store');
Route::post('/shop/{shop:id}/family/', [StoreProductCategory::class, 'inShop'])->name('shop.family.store');


Route::post('/order/', StoreOrder::class)->name('order.store');
Route::patch('/order/{order:id}', UpdateOrder::class)->name('order.update');




Route::post('/warehouse/', StoreWarehouse::class)->name('warehouse.store');
Route::patch('/warehouse/{warehouse:id}', UpdateWarehouse::class)->name('warehouse.update');
Route::delete('/warehouse/{warehouse:id}', DeleteWarehouse::class)->name('warehouse.delete');

Route::post('/warehouse/{warehouse:id}/area/', StoreWarehouseArea::class)->name('warehouse.warehouse-area.store');

Route::patch('/area/{warehouseArea:id}', UpdateWarehouseArea::class)->name('warehouse-area.update');
Route::delete('/area/{warehouseArea:id}', DeleteWarehouseArea::class)->name('warehouse-area.delete');
Route::delete('/warehouse/{warehouse:id}/area/{warehouseArea:id}', [DeleteWarehouseArea::class,'inWarehouse'])->name('warehouse.warehouse-area.delete');

Route::patch('/location/{location:id}', UpdateLocation::class)->name('location.update');
Route::delete('/location/{location:id}', DeleteLocation::class)->name('location.delete');
Route::delete('/warehouse/{warehouse:id}/location/{location:id}', [DeleteLocation::class, 'inWarehouse'])->name('warehouse.location.delete');
Route::delete('/area/{warehouseArea:id}/location/{location:id}', [DeleteLocation::class, 'inWarehouseArea'])->name('warehouse-area.location.delete');
Route::delete('/warehouse/{warehouse:id}/area/{warehouseArea:id}/location/{location:id}', [DeleteLocation::class, 'inWarehouseInWarehouseArea'])->name('warehouse.warehouse-area.location.delete');

Route::post('/warehouse/{warehouse:id}/location', StoreLocation::class)->name('warehouse.location.store');
Route::post('/area/{warehouseArea:id}/location', [StoreLocation::class, 'inWarehouseArea'])->name('warehouse-area.location.store');

Route::patch('/stock/{stock:id}', UpdateStock::class)->name('stock.update');
Route::post('/stock-family', StoreStockFamily::class)->name('stock-family.store');
Route::patch('/stock-family/{stockFamily:id}', UpdateStockFamily::class)->name('stock-family.update');
Route::delete('/stock-family/{stockFamily:id}', DeleteStockFamily::class)->name('stock-family.delete');
Route::post('/stock-family/{stockFamily:id}/stock', [StoreStock::class,'inStockFamily'])->name('stock-family.stock.store');
Route::patch('/stock-family/{stockFamily:id}/stock/{stock:id}', [UpdateStock::class,'inStockFamily'])->name('stock-family.stock.update');
Route::delete('/stock-family/{stockFamily:id}/stock/{stock:id}', [DeleteStock::class, 'inStockFamily'])->name('stock-family.stock.delete');

Route::patch('/agent/{agent:id}', UpdateAgent::class)->name('agent.update');
Route::post('/agent/{agent:id}/purchase-order', [StorePurchaseOrder::class, 'inAgent'])->name('agent.purchase-order.store');
Route::delete('/agent/{agent:id}', DeleteAgent::class)->name('agent.delete');



Route::patch('/supplier/{supplier:id}', UpdateSupplier::class)->name('supplier.update');
Route::delete('/supplier/{supplier:id}', DeleteSupplier::class)->name('supplier.delete');


Route::post('/agent/{agent:id}/supplier', [StoreSupplier::class, 'inAgent'])->name('agent.supplier.store');
Route::post('/agent/{supplier:id}/purchase-order', [StorePurchaseOrder::class, 'inSupplier'])->name('supplier.purchase-order.store');
Route::post('/supplier/{supplier:id}/purchase-order', [StorePurchaseOrder::class, 'inSupplier'])->name('supplier.purchase-order.store');


Route::post('/provider', StoreOrgPaymentServiceProvider::class)->name('payment-service-provider.store');
Route::patch('/provider/{paymentServiceProvider:id}', UpdatePaymentServiceProvider::class)->name('payment-service-provider.update');
Route::delete('/provider/{paymentServiceProvider:id}', DeletePaymentServiceProvider::class)->name('payment-service-provider.delete');

Route::patch('/payment/{payment:id}', UpdatePayment::class)->name('payment.update');




Route::patch('/guest/{guest:id}', UpdateGuest::class)->name('guest.update');
Route::post('/guest/', StoreGuest::class)->name('guest.store');
Route::delete('/guest/{guest:id}', DeleteGuest::class)->name('guest.delete');

Route::patch('/outbox/{outbox:id}', UpdateOutbox::class)->name('outbox.update');

Route::patch('/purchase-order/{purchaseOrder:id}', UpdatePurchaseOrder::class)->name('purchase-order.update');

Route::patch('/supplier-delivery/{stockDelivery:id}', UpdateStockDelivery::class)->name('supplier-delivery.update');
Route::post('/supplier-delivery/', StoreStockDelivery::class)->name('supplier-delivery.store');
Route::patch('/marketplace-agent/{marketplaceAgent:id}', UpdateMarketplaceAgent::class)->name('marketplace-agent.update');
Route::delete('/marketplace-agent/{marketplaceAgent:id}', DeleteMarketplaceAgent::class)->name('marketplace-agent.delete');

Route::patch('/marketplace-supplier/{marketplaceSupplier:id}', UpdateMarketplaceSupplier::class)->name('marketplace-supplier.update');

Route::patch('/system-settings', UpdateOrganisation::class)->name('system-settings.update');
*/
