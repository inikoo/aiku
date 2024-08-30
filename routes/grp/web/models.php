<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 10:25:11 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */


use App\Actions\Accounting\OrgPaymentServiceProvider\StoreOrgPaymentServiceProvider;
use App\Actions\Accounting\OrgPaymentServiceProvider\StoreOrgPaymentServiceProviderAccount;
use App\Actions\Accounting\Payment\StorePayment;
use App\Actions\Accounting\PaymentAccount\StorePaymentAccount;
use App\Actions\Accounting\PaymentAccount\UpdatePaymentAccount;
use App\Actions\Catalogue\Collection\AttachCollectionToModels;
use App\Actions\Catalogue\Collection\StoreCollection;
use App\Actions\Catalogue\Collection\UpdateCollection;
use App\Actions\Catalogue\Product\DeleteImagesFromProduct;
use App\Actions\Catalogue\Product\DeleteProduct;
use App\Actions\Catalogue\Product\StoreProduct;
use App\Actions\Catalogue\Product\UpdateProduct;
use App\Actions\Catalogue\Product\UploadImagesToProduct;
use App\Actions\Catalogue\ProductCategory\StoreProductCategory;
use App\Actions\Catalogue\ProductCategory\UpdateProductCategory;
use App\Actions\Catalogue\Service\StoreService;
use App\Actions\Catalogue\Service\UpdateService;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Catalogue\Shop\SyncPaymentAccountToShop;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\CRM\Customer\AddDeliveryAddressToCustomer;
use App\Actions\CRM\Customer\DeleteCustomerDeliveryAddress;
use App\Actions\CRM\Customer\DeletePortfolio;
use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\Customer\UpdateCustomer;
use App\Actions\CRM\Customer\UpdateCustomerAddress;
use App\Actions\CRM\Customer\UpdateCustomerDeliveryAddress;
use App\Actions\CRM\CustomerClient\StoreCustomerClient;
use App\Actions\CRM\Prospect\ImportShopProspects;
use App\Actions\CRM\WebUser\StoreWebUser;
use App\Actions\CRM\WebUser\UpdateWebUser;
use App\Actions\Dropshipping\Portfolio\StorePortfolio;
use App\Actions\Fulfilment\Fulfilment\StoreFulfilmentFromUI;
use App\Actions\Fulfilment\Fulfilment\UpdateFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\AddDeliveryAddressToFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\StoreFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\UpdateFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentTransaction\DeleteFulfilmentTransaction;
use App\Actions\Fulfilment\FulfilmentTransaction\StoreFulfilmentTransaction;
use App\Actions\Fulfilment\FulfilmentTransaction\UpdateFulfilmentTransaction;
use App\Actions\Fulfilment\Pallet\BookInPallet;
use App\Actions\Fulfilment\Pallet\DeletePallet;
use App\Actions\Fulfilment\Pallet\DeletePalletInDelivery;
use App\Actions\Fulfilment\Pallet\ImportPallet;
use App\Actions\Fulfilment\Pallet\SetPalletAsDamaged;
use App\Actions\Fulfilment\Pallet\SetPalletAsLost;
use App\Actions\Fulfilment\Pallet\SetPalletAsNotReceived;
use App\Actions\Fulfilment\Pallet\SetPalletInReturnAsPicked;
use App\Actions\Fulfilment\Pallet\SetPalletRental;
use App\Actions\Fulfilment\Pallet\StoreMultiplePalletsFromDelivery;
use App\Actions\Fulfilment\Pallet\StorePalletFromDelivery;
use App\Actions\Fulfilment\Pallet\AttachPalletsToReturn;
use App\Actions\Fulfilment\Pallet\ImportPalletReturnItem;
use App\Actions\Fulfilment\Pallet\UndoBookedInPallet;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\Pallet\UpdatePalletLocation;
use App\Actions\Fulfilment\PalletDelivery\CancelPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\ConfirmPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\Pdf\PdfPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\ReceivedPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\SetPalletDeliveryAsBookedIn;
use App\Actions\Fulfilment\PalletDelivery\StartBookingPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\SubmitAndConfirmPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\UpdatePalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\UpdatePalletDeliveryTimeline;
use App\Actions\Fulfilment\PalletReturn\ConfirmPalletReturn;
use App\Actions\Fulfilment\PalletReturn\DetachPalletFromReturn;
use App\Actions\Fulfilment\PalletReturn\DispatchedPalletReturn;
use App\Actions\Fulfilment\PalletReturn\Pdf\PdfPalletReturn;
use App\Actions\Fulfilment\PalletReturn\PickedPalletReturn;
use App\Actions\Fulfilment\PalletReturn\PickingPalletReturn;
use App\Actions\Fulfilment\PalletReturn\StorePalletReturn;
use App\Actions\Fulfilment\PalletReturn\SubmitAndConfirmPalletReturn;
use App\Actions\Fulfilment\PalletReturn\UpdatePalletReturn;
use App\Actions\Fulfilment\PalletReturnItem\NotPickedPalletFromReturn;
use App\Actions\Fulfilment\PalletReturnItem\SyncPalletReturnItem;
use App\Actions\Fulfilment\PalletReturnItem\UndoPickingPalletFromReturn;
use App\Actions\Fulfilment\RecurringBill\ConsolidateRecurringBill;
use App\Actions\Fulfilment\RecurringBill\UpdateRecurringBilling;
use App\Actions\Fulfilment\Rental\StoreRental;
use App\Actions\Fulfilment\Rental\UpdateRental;
use App\Actions\Fulfilment\RentalAgreement\StoreRentalAgreement;
use App\Actions\Fulfilment\RentalAgreement\UpdateRentalAgreement;
use App\Actions\Fulfilment\StoredItem\DeleteStoredItem;
use App\Actions\Fulfilment\StoredItem\DeleteStoredItemFromReturn;
use App\Actions\Fulfilment\StoredItem\MoveStoredItem;
use App\Actions\Fulfilment\StoredItem\ResetAuditStoredItemToPallet;
use App\Actions\Fulfilment\StoredItem\StoreStoredItem;
use App\Actions\Fulfilment\StoredItem\StoreStoredItemsToReturn;
use App\Actions\Fulfilment\StoredItem\SyncStoredItemPallet;
use App\Actions\Fulfilment\StoredItem\SyncStoredItemToPallet;
use App\Actions\Fulfilment\StoredItem\SyncStoredItemToPalletAudit;
use App\Actions\Fulfilment\StoredItem\UpdateStoredItem;
use App\Actions\Fulfilment\StoredItemAudit\StoreStoredItemAudit;
use App\Actions\Fulfilment\StoredItemAudit\UpdateStoredItemAudit;
use App\Actions\Helpers\GoogleDrive\AuthorizeClientGoogleDrive;
use App\Actions\Helpers\GoogleDrive\CallbackClientGoogleDrive;
use App\Actions\Helpers\Tag\StoreTag;
use App\Actions\HumanResources\ClockingMachine\DeleteClockingMachine;
use App\Actions\HumanResources\ClockingMachine\StoreClockingMachine;
use App\Actions\HumanResources\ClockingMachine\UpdateClockingMachine;
use App\Actions\HumanResources\Employee\DeleteEmployee;
use App\Actions\HumanResources\Employee\StoreEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployee;
use App\Actions\HumanResources\Employee\UpdateEmployeeOtherOrganisationJobPositions;
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
use App\Actions\Mail\Mailshot\StoreMailshot;
use App\Actions\Mail\Mailshot\UpdateMailshot;
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
use App\Actions\Ordering\Order\StoreOrder;
use App\Actions\SupplyChain\Agent\StoreAgent;
use App\Actions\SupplyChain\Supplier\StoreSupplier;
use App\Actions\SysAdmin\Group\UpdateGroupSettings;
use App\Actions\SysAdmin\Guest\DeleteGuest;
use App\Actions\SysAdmin\Guest\StoreGuest;
use App\Actions\SysAdmin\Guest\UpdateGuest;
use App\Actions\SysAdmin\Organisation\StoreOrganisation;
use App\Actions\SysAdmin\Organisation\UpdateOrganisationSettings;
use App\Actions\SysAdmin\User\UpdateUser;
use App\Actions\UI\Notification\MarkAllNotificationAsRead;
use App\Actions\UI\Notification\MarkNotificationAsRead;
use App\Actions\UI\Profile\GetProfileAppLoginQRCode;
use App\Actions\UI\Profile\UpdateProfile;
use App\Actions\Web\Banner\DeleteBanner;
use App\Actions\Web\Banner\PublishBanner;
use App\Actions\Web\Banner\StoreBanner;
use App\Actions\Web\Banner\UpdateBanner;
use App\Actions\Web\Banner\UpdateBannerState;
use App\Actions\Web\Banner\UploadImagesToBanner;
use App\Actions\Web\ModelHasWebBlocks\DeleteModelHasWebBlocks;
use App\Actions\Web\ModelHasWebBlocks\StoreModelHasWebBlock;
use App\Actions\Web\ModelHasWebBlocks\UpdateModelHasWebBlocks;
use App\Actions\Web\ModelHasWebBlocks\UploadImagesToModelHasWebBlocks;
use App\Actions\Web\Webpage\PublishWebpage;
use App\Actions\Web\Webpage\ReorderWebBlocks;
use App\Actions\Web\Webpage\UpdateWebpage;
use App\Actions\Web\Website\LaunchWebsite;
use App\Actions\Web\Website\PublishWebsiteMarginal;
use App\Actions\Web\Website\StoreWebsite;
use App\Actions\Web\Website\UpdateWebsite;
use App\Actions\Web\Website\UploadImagesToWebsite;
use App\Stubs\UIDummies\ImportDummy;
use Illuminate\Support\Facades\Route;

Route::patch('/profile', UpdateProfile::class)->name('profile.update');
Route::get('/profile/app-login-qrcode', GetProfileAppLoginQRCode::class)->name('profile.app-login-qrcode');

Route::patch('/user/{user:id}', UpdateUser::class)->name('user.update');
Route::patch('/user/{user:id}/organisation/{organisation:id}', UpdateEmployeeOtherOrganisationJobPositions::class)->name('user.other-organisation.update')->withoutScopedBindings();
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


Route::prefix('position/{jobPosition:id}')->name('job_position.')->group(function () {
    Route::patch('', UpdateJobPosition::class)->name('update');
    Route::delete('', DeleteJobPosition::class)->name('delete');
});

Route::prefix('clocking-machine/{clockingMachine:id}')->name('clocking_machine..')->group(function () {
    Route::patch('', UpdateClockingMachine::class)->name('update');
    Route::delete('', DeleteClockingMachine::class)->name('delete');
});



Route::patch('fulfilment/{fulfilment:id}', UpdateFulfilment::class)->name('fulfilment.update');
Route::patch('customer/{customer:id}', UpdateCustomer::class)->name('customer.update')->withoutScopedBindings();
Route::patch('customer/delivery-address/{customer:id}', UpdateCustomerDeliveryAddress::class)->name('customer.delivery-address.update')->withoutScopedBindings();


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

    Route::post("google-drive.authorize", [AuthorizeClientGoogleDrive::class, 'authorize'])->name('google_drive.authorize');
    Route::get("google-drive.callback", CallbackClientGoogleDrive::class)->name('google_drive.callback');
    Route::patch("settings", UpdateOrganisationSettings::class)->name('settings.update');


    Route::post('employee', StoreEmployee::class)->name('employee.store');
    Route::post('position', StoreJobPosition::class)->name('jon_position.store');
    Route::post('working-place', StoreWorkplace::class)->name('workplace.store');
    Route::post('clocking-machine', [StoreClockingMachine::class, 'inOrganisation'])->name('clocking-machine.store');


    Route::post('shop', StoreShop::class)->name('shop.store');
    Route::patch('shop/{shop:id}', UpdateShop::class)->name('shop.update')->withoutScopedBindings();
    Route::post('fulfilment', StoreFulfilmentFromUI::class)->name('fulfilment.store');


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
        Route::post('/', StoreProductCategory::class)->name('store')->withoutScopedBindings();
        Route::patch('{productCategory:id}', UpdateProductCategory::class)->name('update')->withoutScopedBindings();
        Route::post('family/store/{productCategory:id}', [StoreProductCategory::class, 'inDepartment'])->name('family.store')->withoutScopedBindings();
        Route::post('sub-department/store/{productCategory:id}', [StoreProductCategory::class, 'inDepartment'])->name('sub-department.store')->withoutScopedBindings();
        Route::patch('sub-department/update/{productCategory:id}', UpdateProductCategory::class)->name('sub-department.update')->withoutScopedBindings();
    });

    Route::prefix('/shop/{shop:id}/catalogue/families')->name('catalogue.families.')->group(function () {
        Route::post('{family:id}/product/store', [StoreProduct::class, 'inFamily'])->name('product.store')->withoutScopedBindings();
        Route::patch('{productCategory:id}', UpdateProductCategory::class)->name('update')->withoutScopedBindings();
    });

    Route::post('/shop/{shop:id}/customer', StoreCustomer::class)->name('shop.customer.store');


    Route::post('/shop/{shop:id}/product/', [StoreProduct::class, 'inShop'])->name('show.product.store');
    Route::delete('/shop/{shop:id}/product/{product:id}', [DeleteProduct::class, 'inShop'])->name('shop.product.delete');

    Route::delete('shop/{shop:id}/customer/{customer:id}/portfolio/{portfolio:id}', DeletePortfolio::class)->name('shop.customer.portfolio.delete')->withoutScopedBindings();
    Route::post('shop/{shop:id}/customer/{customer:id}/portfolio', StorePortfolio::class)->name('shop.customer.portfolio.store')->withoutScopedBindings();

    Route::post('/product/', StoreProduct::class)->name('product.store');
    Route::patch('/product/{product:id}', UpdateProduct::class)->name('product.update');
    Route::delete('/product/{product:id}', UpdateProduct::class)->name('product.delete');

    Route::post('product/{product:id}/images', UploadImagesToProduct::class)->name('product.images.store')->withoutScopedBindings();
    Route::delete('product/{product:id}/images/{media:id}/media', DeleteImagesFromProduct::class)->name('product.images.delete')->withoutScopedBindings();

    Route::patch('/payment-account/{paymentAccount:id}', UpdatePaymentAccount::class)->name('payment-account.update')->withoutScopedBindings();
    Route::post('/payment-account', StorePaymentAccount::class)->name('payment-account.store');
    Route::post('/payment-service-provider/{paymentServiceProvider:id}', StoreOrgPaymentServiceProvider::class)->name('payment-service-provider.store')->withoutScopedBindings();

    Route::post('/payment-service-provider/{paymentServiceProvider:id}/account', StoreOrgPaymentServiceProviderAccount::class)->name('payment-service-provider-account.store')->withoutScopedBindings();
});

Route::name('fulfilment-transaction.')->prefix('fulfilment_transaction/{fulfilmentTransaction:id}')->group(function () {
    Route::patch('', UpdateFulfilmentTransaction::class)->name('update');
    Route::delete('', DeleteFulfilmentTransaction::class)->name('delete');
});

Route::name('recurring-bill.')->prefix('recurring-bill/{recurringBill:id}')->group(function () {
    Route::patch('', UpdateRecurringBilling::class)->name('update');
    Route::patch('consolidate', ConsolidateRecurringBill::class)->name('consolidate');
});

Route::name('pallet-delivery.')->prefix('pallet-delivery/{palletDelivery:id}')->group(function () {
    Route::patch('/', UpdatePalletDelivery::class)->name('update');
    Route::post('submit-and-confirm', SubmitAndConfirmPalletDelivery::class)->name('submit_and_confirm');
    Route::post('cancel', CancelPalletDelivery::class)->name('cancel');

    Route::post('confirm', ConfirmPalletDelivery::class)->name('confirm');
    Route::post('received', ReceivedPalletDelivery::class)->name('received');
    Route::post('booking', StartBookingPalletDelivery::class)->name('booking');
    Route::post('booked-in', SetPalletDeliveryAsBookedIn::class)->name('booked-in');


    Route::post('pallet-upload', [ImportPallet::class, 'fromGrp'])->name('pallet.upload');
    Route::post('pallet', StorePalletFromDelivery::class)->name('pallet.store');
    Route::post('multiple-pallet', StoreMultiplePalletsFromDelivery::class)->name('multiple-pallets.store');

    Route::post('transaction', [StoreFulfilmentTransaction::class,'inPalletDelivery'])->name('transaction.store');

    Route::get('pdf', PdfPalletDelivery::class)->name('pdf');
});

Route::name('pallet-return.')->prefix('pallet-return/{palletReturn:id}')->group(function () {

    Route::post('transaction', [StoreFulfilmentTransaction::class,'inPalletReturn'])->name('transaction.store');
    Route::post('pallet', AttachPalletsToReturn::class)->name('pallet.store');
    //todo this new action
    Route::post('stored-item', StoreStoredItemsToReturn::class)->name('stored_item.store');
    Route::post('stored-item-upload', [ImportPalletReturnItem::class, 'fromGrp'])->name('stored-item.upload');
    Route::post('pallet-upload', [ImportPallet::class, 'fromGrp'])->name('pallet.upload');
    Route::patch('/', UpdatePalletReturn::class)->name('update');
    Route::get('pdf', PdfPalletReturn::class)->name('pdf');
});

Route::name('pallet.')->prefix('pallet/{pallet:id}')->group(function () {
    Route::delete('', DeletePallet::class)->name('delete');
    Route::patch('', UpdatePallet::class)->name('update');
    Route::patch('rental', SetPalletRental::class)->name('rental.update');

    Route::patch('pallet-return-item', SyncPalletReturnItem::class)->name('pallet-return-item.sync');

    Route::post('stored-items', SyncStoredItemToPallet::class)->name('stored-items.update');
    Route::post('stored-items/audit', SyncStoredItemToPalletAudit::class)->name('stored-items.audit');
    Route::delete('stored-items/reset', ResetAuditStoredItemToPallet::class)->name('stored-items.audit.reset');
    Route::patch('book-in', BookInPallet::class)->name('book_in');
    Route::patch('not-received', SetPalletAsNotReceived::class)->name('not-received');
    Route::patch('undo-not-received', UndoBookedInPallet::class)->name('undo-not-received');
    Route::patch('undo-booked-in', UndoBookedInPallet::class)->name('undo_book_in');

    Route::patch('damaged', SetPalletAsDamaged::class)->name('damaged');
    Route::patch('lost', SetPalletAsLost::class)->name('lost');
});

Route::name('pallet-return-item.')->prefix('pallet-return-item/{palletReturnItem}')->group(function () {
    Route::patch('', SetPalletInReturnAsPicked::class)->name('update');
    Route::patch('not-picked', NotPickedPalletFromReturn::class)->name('not-picked');
    Route::patch('undo-picking', UndoPickingPalletFromReturn::class)->name('undo-picking');
});

Route::patch('{storedItem:id}/stored-items/pallets', SyncStoredItemPallet::class)->name('stored-items.pallets.update');
Route::patch('{storedItem:id}/stored-items', MoveStoredItem::class)->name('stored-items.move');
Route::delete('{storedItem:id}/stored-items', DeleteStoredItem::class)->name('stored-items.delete');

Route::name('fulfilment-customer.')->prefix('fulfilment-customer/{fulfilmentCustomer:id}')->group(function () {
    Route::patch('', UpdateFulfilmentCustomer::class)->name('update')->withoutScopedBindings();

    Route::post('stored-items', StoreStoredItem::class)->name('stored-items.store');
    Route::patch('', UpdateFulfilmentCustomer::class)->name('update');
    Route::post('pallet-delivery', StorePalletDelivery::class)->name('pallet-delivery.store');
    Route::delete('pallet-delivery/{palletDelivery:id}/pallet/{pallet:id}', DeletePalletInDelivery::class)->name('pallet-delivery.pallet.delete');
    Route::get('pallet-delivery/{palletDelivery:id}/export', PdfPalletDelivery::class)->name('pallet-delivery.export');
    Route::patch('pallet-delivery/{palletDelivery:id}/timeline', UpdatePalletDeliveryTimeline::class)->name('pallet-delivery.timeline.update');
    Route::post('pallet-return', StorePalletReturn::class)->name('pallet-return.store');
    Route::post('pallet-return-stored-items', [StorePalletReturn::class,'withStoredItems'])->name('pallet-return-stored-items.store');

    Route::post('', [StoreWebUser::class, 'inFulfilmentCustomer'])->name('web-user.store');



    Route::post('address', AddDeliveryAddressToFulfilmentCustomer::class)->name('address.store');
    Route::delete('address/{address:id}/delete', DeleteCustomerDeliveryAddress::class)->name('delivery-address.delete')->withoutScopedBindings();
    Route::patch('address/update', [UpdateCustomerAddress::class, 'fromFulfilmentCustomer'])->name('address.update');

    Route::prefix('pallet-return/{palletReturn:id}')->name('pallet-return.')->group(function () {
        Route::prefix('pallet/{pallet:id}')->group(function () {
            Route::delete('', DetachPalletFromReturn::class)->name('pallet.delete');
        });

        Route::prefix('stored-item/{palletReturnItem:id}')->group(function () {
            Route::delete('', DeleteStoredItemFromReturn::class)->name('stored-item.delete')->withoutScopedBindings();
        });

        Route::post('submit-and-confirm', SubmitAndConfirmPalletReturn::class)->name('submit_and_confirm');
        Route::post('delivery', PickingPalletReturn::class)->name('picking');
        Route::post('confirm', ConfirmPalletReturn::class)->name('confirm');
        Route::post('received', PickedPalletReturn::class)->name('picked');
        Route::post('dispatched', DispatchedPalletReturn::class)->name('dispatched');
    });


    Route::prefix('rental-agreements')->name('rental-agreements.')->group(function () {
        Route::post('/', StoreRentalAgreement::class)->name('store');
    });

    Route::prefix('stored-item-audits')->name('stored_item_audits.')->group(function () {
        Route::post('/', StoreStoredItemAudit::class)->name('store');
        Route::patch('/{storedItemAudit:id}', UpdateStoredItemAudit::class)->name('update')->withoutScopedBindings();
    });
});

Route::prefix('rental-agreement/{rentalAgreement:id}')->group(function () {
    Route::patch('', UpdateRentalAgreement::class)->name('rental-agreement.update');
});

Route::name('shop.')->prefix('shop/{shop:id}')->group(function () {
    Route::post('prospect/upload', [ImportShopProspects::class, 'inShop'])->name('prospects.upload');
    Route::post('website', StoreWebsite::class)->name('website.store');

    Route::name('webpage.')->prefix('webpage/{webpage:id}')->group(function () {
        Route::patch('', [UpdateWebpage::class, 'inShop'])->name('update')->withoutScopedBindings();
    });

    Route::prefix('website/{website:id}/banner')->name('website.banner.')->group(function () {
        Route::post('/', StoreBanner::class)->name('store')->withoutScopedBindings();
        Route::post('from-gallery', [StoreBanner::class, 'fromGallery'])->name('store.from-gallery');

        Route::prefix('{banner:id}')->group(function () {
            Route::post('images', UploadImagesToBanner::class)->name('images.store')->withoutScopedBindings();
            Route::patch('', UpdateBanner::class)->name('update')->withoutScopedBindings();
            Route::patch('publish', PublishBanner::class)->name('publish')->withoutScopedBindings();
            Route::patch('state/{state}', UpdateBannerState::class)->name('update-state');
            Route::delete('', DeleteBanner::class)->name('delete');
            Route::patch('shutdown', PublishBanner::class)->name('shutdown');
            Route::patch('switch-on', PublishBanner::class)->name('switch-on');
        });
    });
});

Route::name('fulfilment.')->prefix('fulfilment/{fulfilment:id}')->group(function () {
    Route::post('website', [StoreWebsite::class, 'inFulfilment'])->name('website.store');
    Route::post('fulfilment-customer', StoreFulfilmentCustomer::class)->name('fulfilment_customer.store');
    Route::patch('website/{website:id}', [UpdateWebsite::class, 'inFulfilment'])->name('website.update')->withoutScopedBindings();


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
    Route::post('publish/header', [PublishWebsiteMarginal::class, 'header'])->name('publish.header');
    Route::post('publish/footer', [PublishWebsiteMarginal::class, 'footer'])->name('publish.footer');

    Route::patch('autosave/header', [PublishWebsiteMarginal::class, 'header'])->name('autosave.header');
    Route::patch('autosave/footer', [PublishWebsiteMarginal::class, 'footer'])->name('autosave.footer');

    Route::post('publish/menu', [PublishWebsiteMarginal::class, 'menu'])->name('publish.menu');

    Route::patch('color', [PublishWebsiteMarginal::class, 'color'])->name('update.color');

    Route::patch('', UpdateWebsite::class)->name('update');
    Route::post('launch', LaunchWebsite::class)->name('launch');
    Route::post('images/header', [UploadImagesToWebsite::class, 'header'])->name('header.images.store');
    Route::post('images/footer', [UploadImagesToWebsite::class, 'footer'])->name('footer.images.store');
    Route::post('images/favicon', [UploadImagesToWebsite::class, 'favicon'])->name('favicon.images.store');
});
Route::name('webpage.')->prefix('webpage/{webpage:id}')->group(function () {
    Route::post('publish', PublishWebpage::class)->name('publish');
    Route::post('web-block', StoreModelHasWebBlock::class)->name('web_block.store');
    Route::post('reorder-web-blocks', ReorderWebBlocks::class)->name('reorder_web_blocks');
});

Route::name('model_has_web_block.')->prefix('model-has-web-block/{modelHasWebBlocks:id}')->group(function () {
    Route::patch('', UpdateModelHasWebBlocks::class)->name('update');
    Route::delete('', DeleteModelHasWebBlocks::class)->name('delete');
    Route::post('images', UploadImagesToModelHasWebBlocks::class)->name('images.store');
});

Route::patch('/web-user/{webUser:id}', UpdateWebUser::class)->name('web-user.update');

Route::name('customer.')->prefix('customer/{customer:id}')->group(function () {
    Route::post('', [StoreWebUser::class, 'inCustomer'])->name('web-user.store');
    Route::post('address', AddDeliveryAddressToCustomer::class)->name('address.store');
    Route::patch('address/update', UpdateCustomerAddress::class)->name('address.update');
    Route::delete('address/{address:id}/delete', [DeleteCustomerDeliveryAddress::class, 'inCustomer'])->name('delivery-address.delete')->withoutScopedBindings();
    Route::post('payment/invoice/{paymentAccount:id}/{scope:id}', StorePayment::class)->name('payment.invoice.store')->withoutScopedBindings();
    Route::post('payment/order/{paymentAccount:id}/{scope:id}', [StorePayment::class, 'inOrder'])->name('payment.order.store')->withoutScopedBindings();
    Route::post('client', StoreCustomerClient::class)->name('client.store');
    Route::post('order', [StoreOrder::class, 'inCustomer'])->name('order.store');
});

Route::name('customer-client.')->prefix('customer-client/{customerClient:id}')->group(function () {
    Route::post('order', [StoreOrder::class, 'inCustomerClient'])->name('order.store');
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


Route::patch('stored-items/{storedItem:id}', UpdateStoredItem::class)->name('stored-items.update');

Route::patch('/group-settings', UpdateGroupSettings::class)->name('group-settings.update');

Route::patch('/{mailshot:id}/mailshot', UpdateMailshot::class)->name('shop.mailshot.update');
Route::post('/shop/{shop:id}/mailshot', StoreMailshot::class)->name('shop.mailshot.store');

Route::patch('/guest/{guest:id}', UpdateGuest::class)->name('guest.update');
Route::post('/guest/', StoreGuest::class)->name('guest.store');
Route::delete('/guest/{guest:id}', DeleteGuest::class)->name('guest.delete');

Route::name('collection.')->prefix('collection/{collection:id}')->group(function () {
    Route::post('attach-models', AttachCollectionToModels::class)->name('attach-models');
});

require __DIR__."/models/inventory/location_org_stock.php";
require __DIR__."/models/ordering/order.php";


/*




Route::delete('/shop/{shop:id}', DeleteShop::class)->name('shop.delete');

Route::post('/shop/{shop:id}/customer/', StoreCustomer::class)->name('shop.customer.store');
Route::post('/shop/{shop:id}/department/', [StoreProductCategory::class, 'inShop'])->name('shop.department.store');
Route::post('/shop/{shop:id}/website/', StoreWebsite::class)->name('shop.website.store');
Route::delete('/shop/{shop:id}/department/{department:id}', [DeleteProductCategory::class, 'inShop'])->name('shop.department.delete');


Route::post('stored-items/customer/{customer:id}', StoreStoredItem::class)->name('stored-items.store');


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






Route::patch('/outbox/{outbox:id}', UpdateOutbox::class)->name('outbox.update');

Route::patch('/purchase-order/{purchaseOrder:id}', UpdatePurchaseOrder::class)->name('purchase-order.update');

Route::patch('/supplier-delivery/{stockDelivery:id}', UpdateStockDelivery::class)->name('supplier-delivery.update');
Route::post('/supplier-delivery/', StoreStockDelivery::class)->name('supplier-delivery.store');
Route::patch('/marketplace-agent/{marketplaceAgent:id}', UpdateMarketplaceAgent::class)->name('marketplace-agent.update');
Route::delete('/marketplace-agent/{marketplaceAgent:id}', DeleteMarketplaceAgent::class)->name('marketplace-agent.delete');

Route::patch('/marketplace-supplier/{marketplaceSupplier:id}', UpdateMarketplaceSupplier::class)->name('marketplace-supplier.update');

*/
