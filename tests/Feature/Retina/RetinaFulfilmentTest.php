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
use App\Actions\Fulfilment\Pallet\StoreMultiplePalletsFromDelivery;
use App\Actions\Fulfilment\RentalAgreement\StoreRentalAgreement;
use App\Actions\Retina\Storage\FulfilmentTransaction\StoreRetinaFulfilmentTransaction;
use App\Actions\Retina\Storage\Pallet\ImportRetinaPallet;
use App\Actions\Retina\Storage\Pallet\StoreRetinaPalletFromDelivery;
use App\Actions\Retina\Storage\PalletDelivery\Pdf\PdfRetinaPalletDelivery;
use App\Actions\Retina\Storage\PalletDelivery\StoreRetinaPalletDelivery;
use App\Actions\Retina\Storage\PalletDelivery\SubmitRetinaPalletDelivery;
use App\Actions\Retina\Storage\PalletDelivery\UpdateRetinaPalletDelivery;
use App\Actions\UI\Retina\Profile\UpdateRetinaProfile;
use App\Actions\Web\Website\LaunchWebsite;
use App\Actions\Web\Website\UI\DetectWebsiteFromDomain;
use App\Enums\Billables\Rental\RentalStateEnum;
use App\Enums\Billables\Rental\RentalUnitEnum;
use App\Enums\Billables\Service\ServiceStateEnum;
use App\Enums\Catalogue\Shop\ShopStateEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementStateEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Models\Billables\Rental;
use App\Models\Billables\Service;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\RentalAgreement;
use App\Models\Helpers\Upload;
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
})->depends('Create Retina Pallet Delivery');
