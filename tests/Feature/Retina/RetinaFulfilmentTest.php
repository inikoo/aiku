<?php

/*
 * author Arya Permana - Kirin
 * created on 17-01-2025-10h-33m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

/** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\Billables\Rental\StoreRental;
use App\Actions\Billables\Service\StoreService;
use App\Actions\Catalogue\Shop\UpdateShop;
use App\Actions\Fulfilment\Pallet\BookInPallet;
use App\Actions\Fulfilment\Pallet\StoreMultiplePalletsFromDelivery;
use App\Actions\Fulfilment\PalletDelivery\ConfirmPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\ReceivedPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\StartBookingPalletDelivery;
use App\Actions\Fulfilment\RentalAgreement\StoreRentalAgreement;
use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Retina\Fulfilment\FulfilmentTransaction\StoreRetinaFulfilmentTransaction;
use App\Actions\Retina\Fulfilment\Pallet\ImportRetinaPallet;
use App\Actions\Retina\Fulfilment\Pallet\StoreRetinaPalletFromDelivery;
use App\Actions\Retina\Fulfilment\Pallet\UpdateRetinaPallet;
use App\Actions\Retina\Fulfilment\PalletDelivery\Pdf\PdfRetinaPalletDelivery;
use App\Actions\Retina\Fulfilment\PalletDelivery\StoreRetinaPalletDelivery;
use App\Actions\Retina\Fulfilment\PalletDelivery\SubmitRetinaPalletDelivery;
use App\Actions\Retina\Fulfilment\PalletDelivery\UpdateRetinaPalletDelivery;
use App\Actions\Retina\Fulfilment\PalletReturn\AttachRetinaPalletsToReturn;
use App\Actions\Retina\Fulfilment\PalletReturn\CancelRetinaPalletReturn;
use App\Actions\Retina\Fulfilment\PalletReturn\DetachRetinaPalletFromReturn;
use App\Actions\Retina\Fulfilment\PalletReturn\ImportRetinaPalletReturnItem;
use App\Actions\Retina\Fulfilment\PalletReturn\StoreRetinaPalletReturn;
use App\Actions\Retina\Fulfilment\PalletReturn\StoreRetinaStoredItemsToReturn;
use App\Actions\Retina\Fulfilment\PalletReturn\SubmitRetinaPalletReturn;
use App\Actions\Retina\Fulfilment\PalletReturn\UpdateRetinaPalletReturn;
use App\Actions\Retina\Fulfilment\StoredItem\StoreRetinaStoredItem;
use App\Actions\Retina\Fulfilment\StoredItem\SyncRetinaStoredItemToPallet;
use App\Actions\Retina\UI\Profile\UpdateRetinaProfile;
use App\Actions\Web\Website\LaunchWebsite;
use App\Actions\Web\Website\UI\DetectWebsiteFromDomain;
use App\Enums\Billables\Rental\RentalStateEnum;
use App\Enums\Billables\Rental\RentalUnitEnum;
use App\Enums\Billables\Service\ServiceStateEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnTypeEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementStateEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Models\Billables\Rental;
use App\Models\Billables\Service;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentTransaction;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\RentalAgreement;
use App\Models\Fulfilment\StoredItem;
use App\Models\Helpers\Upload;
use App\Models\Inventory\Location;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

beforeAll(function () {
    loadDB();
});
beforeEach(function () {

    $this->organisation      = createOrganisation();
    $this->warehouse         = createWarehouse();
    $this->fulfilment        = createFulfilment($this->organisation);
    $this->website           = createWebsite($this->fulfilment->shop);
    if ($this->website->state != WebsiteStateEnum::LIVE) {
        LaunchWebsite::make()->action($this->website);
    }
    $this->shop = $this->fulfilment->shop;
    $this->shop = UpdateShop::make()->action($this->shop, ['state' => ShopStateEnum::OPEN]);
    $this->customer = createCustomer($this->shop);
    $this->fulfilmentCustomer = $this->customer->fulfilmentCustomer;

    list(
        $this->tradeUnit,
        $this->product
    ) = createProduct($this->shop);

    $rental = Rental::first();
    if (!$rental) {
        data_set($storeData, 'code', 'TEST');
        data_set($storeData, 'state', RentalStateEnum::ACTIVE);
        data_set($storeData, 'name', 'test');
        data_set($storeData, 'price', 100);
        data_set($storeData, 'unit', RentalUnitEnum::DAY->value);

        $rental = StoreRental::make()->action(
            $this->shop,
            $storeData
        );
    }

    $this->rental = $rental;

    $service = Service::first();
    if (!$service) {
        data_set($storeData, 'code', 'TEST');
        data_set($storeData, 'state', ServiceStateEnum::ACTIVE);
        data_set($storeData, 'name', 'test');
        data_set($storeData, 'price', 100);
        data_set($storeData, 'unit', RentalUnitEnum::DAY->value);

        $service = StoreService::make()->action(
            $this->shop,
            $storeData
        );
    }

    $this->service = $service;


    $rentalAgreement = RentalAgreement::where('fulfilment_customer_id', $this->customer->fulfilmentCustomer->id)->first();
    if (!$rentalAgreement) {
        data_set($storeData, 'billing_cycle', RentalAgreementBillingCycleEnum::MONTHLY);
        data_set($storeData, 'state', RentalAgreementStateEnum::ACTIVE);
        data_set($storeData, 'username', 'test');
        data_set($storeData, 'email', 'test@aiku.io');



        $rentalAgreement = StoreRentalAgreement::make()->action(
            $this->customer->fulfilmentCustomer,
            $storeData,
        );
    }
    $this->rentalAgreement = $rentalAgreement;

    $location           = $this->warehouse->locations()->first();
    if (!$location) {
        StoreLocation::run(
            $this->warehouse,
            Location::factory()->definition()
        );
        StoreLocation::run(
            $this->warehouse,
            Location::factory()->definition()
        );
    }
    $this->location = $location;

    $this->webUser  = createWebUser($this->customer);

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Retina')]
    );

    DetectWebsiteFromDomain::shouldRun()->with('localhost')->andReturn($this->website);
    actingAs($this->webUser);
});

test('Update Retina Profile', function () {
    $webUser = UpdateRetinaProfile::make()->action($this->webUser, [
        'username' => 'joko',
        'about'    => 'decent human being'
    ]);

    expect($webUser)->toBeInstanceOf(WebUser::class)
        ->and($webUser->username)->toBe('joko')
        ->and($webUser->about)->toBe('decent human being');
});

test('Create Retina Pallet Delivery', function () {
    $fulfilmentCustomer = $this->fulfilmentCustomer;
    $palletDelivery = StoreRetinaPalletDelivery::make()->action($fulfilmentCustomer, []);

    $fulfilmentCustomer->refresh();

    expect($palletDelivery)->toBeInstanceOf(PalletDelivery::class)
        ->and($fulfilmentCustomer->number_pallet_deliveries)->toBe(1);

    return $palletDelivery;
});

test('Add Retina Pallet to PalletDelivery', function (PalletDelivery $palletDelivery) {
    $pallet = StoreRetinaPalletFromDelivery::make()->action($palletDelivery, []);

    $pallet->refresh();

    expect($pallet)->toBeInstanceOf(Pallet::class)
        ->and($pallet->fulfilmentCustomer->number_pallets)->toBe(1);

    return $pallet;
})->depends('Create Retina Pallet Delivery');

test('Create Stored Item', function () {
    $storedItem = StoreRetinaStoredItem::make()->action(
        $this->fulfilmentCustomer,
        [
        'reference' => 'item1'
    ]
    );

    $storedItem2 = StoreRetinaStoredItem::make()->action(
        $this->fulfilmentCustomer,
        [
        'reference' => 'item2'
    ]
    );

    $storedItem3 = StoreRetinaStoredItem::make()->action(
        $this->fulfilmentCustomer,
        [
        'reference' => 'item3'
    ]
    );

    $storedItem->refresh();
    $storedItem2->refresh();
    $storedItem3->refresh();

    expect($storedItem)->toBeInstanceOf(StoredItem::class)
        ->and($storedItem2)->toBeInstanceOf(StoredItem::class)
        ->and($storedItem3)->toBeInstanceOf(StoredItem::class)
        ->and($storedItem->fulfilmentCustomer->number_stored_items)->toBe(3);

    return $storedItem;
});

test('Sync Stored Item to Pallet', function (Pallet $pallet) {
    SyncRetinaStoredItemToPallet::make()->action(
        $pallet,
        [
        'stored_item_ids' => [
            1 => [
                'quantity' => 100
            ]
        ]
    ]
    );

    $pallet->refresh();

    expect($pallet)->toBeInstanceOf(Pallet::class)
        ->and($pallet->number_stored_items)->toBe(1);

    return $pallet;
})->depends('Add Retina Pallet to PalletDelivery');

test('Add Retina Mutiple Pallets to Pallet Delivery', function (PalletDelivery $palletDelivery) {
    StoreMultiplePalletsFromDelivery::make()->action($palletDelivery, [
        'number_pallets' => 9,
        'type'           => PalletTypeEnum::PALLET
    ]);

    $palletDelivery->refresh();

    expect($palletDelivery)->toBeInstanceOf(PalletDelivery::class)
        ->and($palletDelivery->fulfilmentCustomer->number_pallets)->toBe(10);

    return $palletDelivery;
})->depends('Create Retina Pallet Delivery');

test('Sync Stored Item to Pallet (again)', function (PalletDelivery $palletDelivery) {
    $pallet = $palletDelivery->pallets()->skip(1)->first();
    $pallet2 = $palletDelivery->pallets()->skip(2)->first();
    SyncRetinaStoredItemToPallet::make()->action(
        $pallet,
        [
        'stored_item_ids' => [
            2 => [
                'quantity' => 200
            ]
        ]
    ]
    );
    SyncRetinaStoredItemToPallet::make()->action(
        $pallet2,
        [
        'stored_item_ids' => [
            3 => [
                'quantity' => 300
            ]
        ]
    ]
    );

    $pallet->refresh();
    $pallet2->refresh();

    expect($pallet)->toBeInstanceOf(Pallet::class)
        ->and($pallet->number_stored_items)->toBe(1);
    expect($pallet2)->toBeInstanceOf(Pallet::class)
        ->and($pallet2->number_stored_items)->toBe(1);

    return $pallet;
})->depends('Add Retina Mutiple Pallets to Pallet Delivery');

test('Update Retina Pallet Delivery', function (PalletDelivery $palletDelivery) {
    $palletDelivery = UpdateRetinaPalletDelivery::make()->action($palletDelivery, [
        'customer_notes' => 'This and That',
    ]);

    $palletDelivery->refresh();

    expect($palletDelivery)->toBeInstanceOf(PalletDelivery::class)
        ->and($palletDelivery->customer_notes)->toBe('This and That');

    return $palletDelivery;
})->depends('Create Retina Pallet Delivery');

test('Store Retina Fulfilment Transaction in Pallet Delivery', function (PalletDelivery $palletDelivery) {
    StoreRetinaFulfilmentTransaction::make()->action($palletDelivery, [
        'historic_asset_id' => $this->product->current_historic_asset_id,
        'quantity'          => 5
    ]);

    $palletDelivery->refresh();

    expect($palletDelivery)->toBeInstanceOf(PalletDelivery::class)
        ->and($palletDelivery->stats->number_physical_goods)->toBe(1);

    return $palletDelivery;
})->depends('Create Retina Pallet Delivery');

test('Import Pallet (xlsx) for Pallet Delivery', function (PalletDelivery $palletDelivery) {
    Storage::fake('local');

    $tmpPath = 'tmp/uploads/';

    $filePath = base_path('tests/fixtures/pallet.xlsx');
    $file     = new UploadedFile($filePath, 'pallet.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    Storage::fake('local')->put($tmpPath, $file);

    expect($palletDelivery->stats->number_pallets)->toBe(10);

    $upload = ImportRetinaPallet::run($palletDelivery, $file);
    $palletDelivery->refresh();
    expect($upload)->toBeInstanceOf(Upload::class)
        ->and($upload->number_rows)->toBe(1)
        ->and($upload->number_success)->toBe(1)
        ->and($upload->number_fails)->toBe(0)
        ->and($palletDelivery->stats->number_pallets)->toBe(11);


    return $palletDelivery;
})->depends('Create Retina Pallet Delivery');

test('Submit Retina Pallet Delivery', function (PalletDelivery $palletDelivery) {
    $palletDelivery = SubmitRetinaPalletDelivery::make()->action($palletDelivery, []);
    $fulfilmentCustomer = $palletDelivery->fulfilmentCustomer;
    $palletDelivery->refresh();
    $fulfilmentCustomer->refresh();

    expect($palletDelivery)->toBeInstanceOf(PalletDelivery::class)
        ->and($palletDelivery->state)->toBe(PalletDeliveryStateEnum::SUBMITTED)
        ->and($fulfilmentCustomer->number_pallet_deliveries_state_submitted)->toBe(1);

    return $palletDelivery;
})->depends('Create Retina Pallet Delivery');

test('Generate Pallet Delivery PDF', function (PalletDelivery $palletDelivery) {
    $pdf = PdfRetinaPalletDelivery::run($palletDelivery);
    expect($pdf->output())->toBeString();

    return $palletDelivery;
})->depends('Submit Retina Pallet Delivery');

test('Process Pallet Delivery (from aiku)', function (PalletDelivery $palletDelivery) {

    $palletDelivery = ConfirmPalletDelivery::make()->action($palletDelivery);

    expect($palletDelivery)->toBeInstanceOf(PalletDelivery::class)
        ->and($palletDelivery->state)->toBe(PalletDeliveryStateEnum::CONFIRMED);


    $palletDelivery = ReceivedPalletDelivery::make()->action($palletDelivery);

    expect($palletDelivery)->toBeInstanceOf(PalletDelivery::class)
        ->and($palletDelivery->state)->toBe(PalletDeliveryStateEnum::RECEIVED);


    $palletDelivery = StartBookingPalletDelivery::make()->action($palletDelivery);

    expect($palletDelivery)->toBeInstanceOf(PalletDelivery::class)
        ->and($palletDelivery->state)->toBe(PalletDeliveryStateEnum::BOOKING_IN);



    $pallet = $palletDelivery->pallets->first();
    BookInPallet::make()->action($pallet, ['location_id' => $this->location->id]);
    $pallet->refresh();

    expect($pallet->location)->toBeInstanceOf(Location::class)
        ->and($pallet->location->id)->toBe($this->location->id)
        ->and($pallet->state)->toBe(PalletStateEnum::BOOKED_IN);


    return $palletDelivery;
})->depends('Submit Retina Pallet Delivery');

test('Update Retina Pallet to PalletDelivery', function (PalletDelivery $palletDelivery) {
    $pallet = $palletDelivery->pallets->first();
    $pallet = UpdateRetinaPallet::make()->action(
        $pallet,
        [
        'reference'          => '000001-diam-p0001',
        'customer_reference' => 'bruh-01',
        ]
    );

    $pallet->refresh();

    expect($pallet)->toBeInstanceOf(Pallet::class)
        ->and($pallet->reference)->toBe('000001-diam-p0001')
        ->and($pallet->customer_reference)->toBe('bruh-01');

    return $pallet;
})->depends('Process Pallet Delivery (from aiku)');

test('Create Retina Pallet Return', function () {

    $fulfilmentCustomer = $this->fulfilmentCustomer;
    $palletReturn = StoreRetinaPalletReturn::make()->action(
        $fulfilmentCustomer,
        [
            'warehouse_id' => $this->warehouse->id,
        ]
    );

    $fulfilmentCustomer->refresh();

    expect($palletReturn)->toBeInstanceOf(PalletReturn::class)
        ->and($palletReturn->state)->toBe(PalletReturnStateEnum::IN_PROCESS)
        ->and($fulfilmentCustomer->number_pallet_returns)->toBe(1);

    return $palletReturn;
});

test('Update Retina Pallet Return', function (PalletReturn $palletReturn) {

    $palletReturn = UpdateRetinaPalletReturn::make()->action(
        $palletReturn,
        [
            'public_notes' => 'this notes',
        ]
    );

    $palletReturn->refresh();

    expect($palletReturn)->toBeInstanceOf(PalletReturn::class)
        ->and($palletReturn->public_notes)->toBe('this notes');

    return $palletReturn;
})->depends('Create Retina Pallet Return');

test('import pallets in return (xlsx)', function (PalletReturn $palletReturn) {
    Storage::fake('local');

    $tmpPath = 'tmp/uploads/';
    //
    $filePath = base_path('tests/fixtures/returnRetinaPalletItems.xlsx');
    $file     = new UploadedFile($filePath, 'returnRetinaPalletItems.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    Storage::fake('local')->put($tmpPath, $file);
    $palletReturn->refresh();
    expect($palletReturn->pallets()->count())->toBe(0)
        ->and($palletReturn->stats->number_pallets)->toBe(0);
    $upload = ImportRetinaPalletReturnItem::run($palletReturn, $file);
    $palletReturn->refresh();

    expect($upload)->toBeInstanceOf(Upload::class)
        ->and($upload->model)->toBe('PalletReturnItem')
        ->and($upload->original_filename)->toBe('returnRetinaPalletItems.xlsx')
        ->and($upload->number_rows)->toBe(1)
        ->and($upload->number_success)->toBe(1)
        ->and($upload->number_fails)->toBe(0)
        ->and($palletReturn->pallets()->count())->toBe(1)
        ->and($palletReturn->stats->number_pallets)->toBe(1);

    return $palletReturn;
})->depends('Create Retina Pallet Return');

test('Attach Pallet to Retina Pallet Return', function (PalletReturn $palletReturn) {
    $palletReturn = AttachRetinaPalletsToReturn::make()->action(
        $palletReturn,
        [
            'pallets' => [2,3]
        ]
    );

    $palletReturn->refresh();

    expect($palletReturn)->toBeInstanceOf(PalletReturn::class)
        ->and($palletReturn->stats->number_pallets)->toBe(2);

    return $palletReturn;
})->depends('import pallets in return (xlsx)');

test('Detach Pallet to Retina Pallet Return', function (PalletReturn $palletReturn) {
    $pallet = $palletReturn->pallets()->first();
    DetachRetinaPalletFromReturn::make()->action(
        $palletReturn,
        $pallet,
        []
    );

    $palletReturn->refresh();

    expect($palletReturn)->toBeInstanceOf(PalletReturn::class)
        ->and($palletReturn->stats->number_pallets)->toBe(1);

    return $palletReturn;
})->depends('Attach Pallet to Retina Pallet Return');

test('Add Transaction to Retina Pallet Return', function (PalletReturn $palletReturn) {
    $fulfilmentTransaction = StoreRetinaFulfilmentTransaction::make()->action(
        $palletReturn,
        [
            'quantity' => 10,
            'historic_asset_id' => $this->product->current_historic_asset_id
        ]
    );

    $fulfilmentTransaction->refresh();
    $palletReturn->refresh();
    expect($fulfilmentTransaction)->toBeInstanceOf(FulfilmentTransaction::class)
        ->and(intval($fulfilmentTransaction->quantity))->toBe(10)
        ->and($palletReturn->stats->number_physical_goods)->toBe(1);

    return $fulfilmentTransaction;
})->depends('Attach Pallet to Retina Pallet Return');

test('Submit Retina Pallet Return', function (PalletReturn $palletReturn) {

    $palletReturn = SubmitRetinaPalletReturn::make()->action(
        $palletReturn,
        []
    );

    $palletReturn->refresh();

    expect($palletReturn)->toBeInstanceOf(PalletReturn::class)
        ->and($palletReturn->state)->toBe(PalletReturnStateEnum::SUBMITTED);

    return $palletReturn;
})->depends('Detach Pallet to Retina Pallet Return');

test('Cancel Retina Pallet Return', function (PalletReturn $palletReturn) {

    $palletReturn = CancelRetinaPalletReturn::make()->action(
        $palletReturn,
        []
    );

    $palletReturn->refresh();

    expect($palletReturn)->toBeInstanceOf(PalletReturn::class)
        ->and($palletReturn->state)->toBe(PalletReturnStateEnum::CANCEL);

    return $palletReturn;
})->depends('Submit Retina Pallet Return');

test('Create Retina Pallet Return (with stored item)', function (PalletReturn $palletReturn) {

    $fulfilmentCustomer = $this->fulfilmentCustomer;

    $palletReturn = StoreRetinaPalletReturn::make()->actionFromRetinaWithStoredItems(
        $fulfilmentCustomer,
        [
            'warehouse_id' => $this->warehouse->id,
        ]
    );

    $fulfilmentCustomer->refresh();

    expect($palletReturn)->toBeInstanceOf(PalletReturn::class)
        ->and($palletReturn->state)->toBe(PalletReturnStateEnum::IN_PROCESS)
        ->and($palletReturn->type)->toBe(PalletReturnTypeEnum::STORED_ITEM);

    return $palletReturn;
})->depends('Create Retina Pallet Return');

test('Attach Stored Item to Retina Pallet Return (with stored item)', function (PalletReturn $palletReturn) {
    $palletReturn = StoreRetinaStoredItemsToReturn::make()->action(
        $palletReturn,
        [
            'stored_items' => [
                2 => [
                    'quantity' => 200
                ]
            ]
        ]
    );

    $palletReturn->refresh();

    expect($palletReturn)->toBeInstanceOf(PalletReturn::class)
        ->and($palletReturn->stats->number_stored_items)->toBe(1);

    return $palletReturn;
})->depends('Create Retina Pallet Return (with stored item)');
