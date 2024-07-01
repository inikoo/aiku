<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jan 2024 22:04:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Service\StoreService;
use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\UpdateFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\BookInPallet;
use App\Actions\Fulfilment\Pallet\SetPalletAsNotReceived;
use App\Actions\Fulfilment\Pallet\SetPalletRental;
use App\Actions\Fulfilment\Pallet\StoreMultiplePalletsFromDelivery;
use App\Actions\Fulfilment\Pallet\StorePallet;
use App\Actions\Fulfilment\Pallet\StorePalletFromDelivery;
use App\Actions\Fulfilment\PalletDelivery\DeletePalletInDelivery;
use App\Actions\Fulfilment\PalletDelivery\PdfPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\SetPalletDeliveryAsBookedIn;
use App\Actions\Fulfilment\PalletDelivery\ConfirmPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\ReceivedPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\SendPalletDeliveryNotification;
use App\Actions\Fulfilment\PalletDelivery\StartBookingPalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\StorePalletDelivery;
use App\Actions\Fulfilment\PalletDelivery\UpdatePalletDelivery;
use App\Actions\Fulfilment\PalletReturn\SendPalletReturnNotification;
use App\Actions\Fulfilment\PalletReturn\StorePalletReturn;
use App\Actions\Fulfilment\Rental\StoreRental;
use App\Actions\Fulfilment\Rental\UpdateRental;
use App\Actions\Fulfilment\RentalAgreement\StoreRentalAgreement;
use App\Actions\Fulfilment\RentalAgreement\UpdateRentalAgreement;
use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Catalogue\Shop\StoreShop;
use App\Actions\Fulfilment\FulfilmentCustomer\FetchNewWebhookFulfilmentCustomer;
use App\Actions\Fulfilment\FulfilmentCustomer\StoreFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Web\Website\StoreWebsite;
use App\Enums\CRM\Customer\CustomerStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Fulfilment\Rental\RentalUnitEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementBillingCycleEnum;
use App\Enums\Fulfilment\RentalAgreement\RentalAgreementStateEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Models\Catalogue\Service;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\Rental;
use App\Models\Fulfilment\RentalAgreement;
use App\Models\Fulfilment\RentalAgreementStats;
use App\Models\Inventory\Location;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Permission;
use App\Models\SysAdmin\Role;
use App\Models\Web\Website;
use Illuminate\Support\Carbon;

use function Pest\Laravel\actingAs;

beforeAll(function () {
    loadDB();
});


beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->adminGuest   = createAdminGuest($this->organisation->group);
    $this->warehouse    = createWarehouse();
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


    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    actingAs($this->adminGuest->user);
});

test('create fulfilment shop', function () {
    $organisation = $this->organisation;
    $storeData    = Shop::factory()->definition();
    data_set($storeData, 'type', ShopTypeEnum::FULFILMENT->value);
    data_set($storeData, 'warehouses', [$this->warehouse->id]);
    $shop = StoreShop::make()->action($this->organisation, $storeData);
    $organisation->refresh();

    $shopRoles             = Role::where('scope_type', 'Shop')->where('scope_id', $shop->id)->get();
    $shopPermissions       = Permission::where('scope_type', 'Shop')->where('scope_id', $shop->id)->get();
    $fulfilmentRoles       = Role::where('scope_type', 'Fulfilment')->where('scope_id', $shop->fulfilment->id)->get();
    $fulfilmentPermissions = Permission::where('scope_type', 'Fulfilment')->where('scope_id', $shop->fulfilment->id)->get();
    $warehouseRoles        = Role::where('scope_type', 'Warehouse')->where('scope_id', $this->warehouse->id)->get();
    $warehousePermissions  = Permission::where('scope_type', 'Warehouse')->where('scope_id', $this->warehouse->id)->get();

    expect($shop)->toBeInstanceOf(Shop::class)
        ->and($shop->fulfilment)->toBeInstanceOf(Fulfilment::class)
        ->and($organisation->catalogueStats->number_shops)->toBe(1)
        ->and($organisation->catalogueStats->number_shops_type_b2b)->toBe(0)
        ->and($organisation->catalogueStats->number_shops_type_fulfilment)->toBe(1)
        ->and($shopRoles->count())->toBe(0)
        ->and($shopPermissions->count())->toBe(0)
        ->and($fulfilmentRoles->count())->toBe(2)
        ->and($fulfilmentPermissions->count())->toBe(4)
        ->and($warehouseRoles->count())->toBe(6)
        ->and($warehousePermissions->count())->toBe(16);

    $user = $this->adminGuest->user;
    $user->refresh();

    expect($user->getAllPermissions()->count())->toBe(22)
        ->and($user->hasAllRoles(["fulfilment-shop-supervisor-{$shop->fulfilment->id}"]))->toBeTrue()
        ->and($user->hasAllRoles(["shop-admin-$shop->id"]))->toBeFalse()
        ->and($shop->fulfilment->number_warehouses)->toBe(1);


    return $shop->fulfilment;
});


test('create services in fulfilment shop', function (Fulfilment $fulfilment) {
    $service1 = StoreService::make()->action(
        $fulfilment->shop,
        [
            'price' => 100,
            'unit'  => 'job',
            'code'  => 'Ser-01',
            'name'  => 'Service 1',
        ]
    );
    $service2 = StoreService::make()->action(
        $fulfilment->shop,
        [
            'price' => 111,
            'unit'  => 'job',
            'code'  => 'Ser-02',
            'name'  => 'Service 2',
        ]
    );


    expect($service1)->toBeInstanceOf(Service::class)
        ->and($service1->asset)->toBeInstanceOf(Asset::class)
        ->and($service2->organisation->catalogueStats->number_assets_type_service)->toBe(2)
        ->and($service2->organisation->catalogueStats->number_assets)->toBe(2)
        ->and($service2->shop->stats->number_assets)->toBe(2)
        ->and($service2->stats->number_historic_assets)->toBe(1);

    return $service1;
})->depends('create fulfilment shop');

test('create rental product to fulfilment shop', function (Fulfilment $fulfilment) {
    $rental = StoreRental::make()->action(
        $fulfilment->shop,
        [
            'price' => 100,
            'unit'  => RentalUnitEnum::WEEK->value,
            'code'  => 'R00001',
            'name'  => 'Rental Asset A',

        ]
    );

    $rental->refresh();


    expect($rental)->toBeInstanceOf(Rental::class)
        ->and($rental->asset)->toBeInstanceOf(Asset::class)
        ->and($rental->organisation->catalogueStats->number_assets)->toBe(3)
        ->and($rental->organisation->catalogueStats->number_assets_type_rental)->toBe(1)
        ->and($rental->shop->stats->number_assets)->toBe(3)
        ->and($rental->stats->number_historic_assets)->toBe(1);

    return $rental;
})->depends('create fulfilment shop');


test('update rental', function (Rental $rental) {
    $rentalData = [
        'name'        => 'Updated Rental Name',
        'description' => 'Updated Rental Description',
        'rrp'         => 99.99
    ];
    $rental     = UpdateRental::make()->action(rental: $rental, modelData: $rentalData);


    $rental->refresh();


    expect($rental->asset->name)->toBe('Updated Rental Name')
        ->and($rental->asset->stats->number_historic_assets)->toBe(2)
        ->and($rental->stats->number_historic_assets)->toBe(2);

    return $rental;
})->depends('create rental product to fulfilment shop');


test('create second rental product to fulfilment shop', function (Fulfilment $fulfilment) {
    $rental = StoreRental::make()->action(
        $fulfilment->shop,
        [
            'price' => 200,
            'unit'  => RentalUnitEnum::WEEK->value,
            'code'  => 'R00002',
            'name'  => 'Rental Asset B',
        ]
    );

    expect($rental)->toBeInstanceOf(Rental::class)
        ->and($rental->asset)->toBeInstanceOf(Asset::class);

    return $rental;
})->depends('create fulfilment shop');

test('assign auto assign asset to rental', function (Rental $rental) {
    $rental->update([
        'auto_assign_asset'      => 'Pallet',
        'auto_assign_asset_type' => PalletTypeEnum::PALLET->value,
    ]);

    expect($rental->auto_assign_asset)->toBe('Pallet')
        ->and($rental->auto_assign_asset_type)->toBe(PalletTypeEnum::PALLET->value);

    return $rental;
})->depends('create rental product to fulfilment shop');

test('create fulfilment website', function (Fulfilment $fulfilment) {
    $website = StoreWebsite::make()->action(
        $fulfilment->shop,
        Website::factory()->definition(),
    );

    expect($website)->toBeInstanceOf(Website::class)
        ->and($website->state)->toBe(WebsiteStateEnum::IN_PROCESS);

    return $website;
})->depends('create fulfilment shop');


test('create fulfilment customer from customer', function (Fulfilment $fulfilment) {
    $customerData = Customer::factory()->definition();

    $customer = StoreCustomer::make()->action(
        $fulfilment->shop,
        $customerData
    );

    UpdateFulfilmentCustomer::make()->action(
        $customer->fulfilmentCustomer,
        [
            'pallets_storage' => true,
            'items_storage'   => true,
        ]
    );

    $fulfilment->refresh();

    expect($customer)->toBeInstanceOf(Customer::class)
        ->and($customer->fulfilmentCustomer)->toBeInstanceOf(FulfilmentCustomer::class)
        ->and($customer->reference)->toBe('000001')
        ->and($customer->status)->toBe(CustomerStatusEnum::APPROVED)
        ->and($customer->is_fulfilment)->toBeTrue()
        ->and($customer->fulfilmentCustomer->pallets_storage)->toBeTrue()
        ->and($customer->fulfilmentCustomer->items_storage)->toBeTrue()
        ->and($customer->fulfilmentCustomer->dropshipping)->toBeFalse()
        ->and($customer->fulfilmentCustomer->number_pallets)->toBe(0)
        ->and($customer->fulfilmentCustomer->number_stored_items)->toBe(0)
        ->and($fulfilment->stats->number_customers_interest_items_storage)->toBe(1)
        ->and($fulfilment->stats->number_customers_interest_pallets_storage)->toBe(1)
        ->and($fulfilment->stats->number_customers_interest_dropshipping)->toBe(0);

    return $customer->fulfilmentCustomer;
})->depends('create fulfilment shop');

test('create fulfilment customer', function (Fulfilment $fulfilment) {

    $fulfilmentCustomer = StoreFulfilmentCustomer::make()->action(
        $fulfilment,
        [
            'state'     => CustomerStateEnum::ACTIVE,
            'status'    => CustomerStatusEnum::APPROVED,
            'contact_name' => 'jacqueline',
            'company_name' => 'ghost.o',
            'interest'     => ['pallets_storage', 'items_storage', 'dropshipping'],
        ]
    );

    UpdateFulfilmentCustomer::make()->action(
        $fulfilmentCustomer,
        [
            'pallets_storage' => true,
            'items_storage'   => true,
        ]
    );

    $fulfilment->refresh();

    expect($fulfilmentCustomer)->toBeInstanceOf(FulfilmentCustomer::class)
        ->and($fulfilmentCustomer->customer)->toBeInstanceOf(Customer::class)
        ->and($fulfilmentCustomer->customer->status)->toBe(CustomerStatusEnum::APPROVED)
        ->and($fulfilmentCustomer->customer->state)->toBe(CustomerStateEnum::ACTIVE)
        ->and($fulfilmentCustomer->customer->is_fulfilment)->toBeTrue()
        ->and($fulfilmentCustomer->pallets_storage)->toBeTrue()
        ->and($fulfilmentCustomer->items_storage)->toBeTrue()
        ->and($fulfilmentCustomer->dropshipping)->toBeTrue()
        ->and($fulfilmentCustomer->number_pallets)->toBe(0)
        ->and($fulfilmentCustomer->number_stored_items)->toBe(0)
        ->and($fulfilment->stats->number_customers_interest_items_storage)->toBe(2)
        ->and($fulfilment->stats->number_customers_interest_pallets_storage)->toBe(2)
        ->and($fulfilment->stats->number_customers_interest_dropshipping)->toBe(1);

    return $fulfilmentCustomer;
})->depends('create fulfilment shop');

test('create rental agreement', function (FulfilmentCustomer $fulfilmentCustomer) {
    $rentalAgreement = StoreRentalAgreement::make()->action(
        $fulfilmentCustomer,
        [
            'billing_cycle' => RentalAgreementBillingCycleEnum::MONTHLY,
            'pallets_limit' => null,
            'username'      => 'test',
            'email'         => 'test@testmail.com',
            'clauses'       => [
                'rentals' => [
                    [
                        'asset_id'       => $fulfilmentCustomer->fulfilment->rentals->first()->asset_id,
                        'percentage_off' => 10,
                    ],
                    [
                        'asset_id'       => $fulfilmentCustomer->fulfilment->rentals->last()->asset_id,
                        'percentage_off' => 20,
                    ],
                ]
            ]
        ]
    );
    $rentalAgreement->refresh();
    expect($rentalAgreement)->toBeInstanceOf(RentalAgreement::class)
        ->and($fulfilmentCustomer->rentalAgreement)->toBeInstanceOf(RentalAgreement::class)
        ->and($rentalAgreement->state)->toBe(RentalAgreementStateEnum::DRAFT)
        ->and($rentalAgreement->stats)->toBeInstanceOf(RentalAgreementStats::class)
        ->and($rentalAgreement->stats->number_rental_agreement_clauses)->toBe(2)
        ->and($rentalAgreement->stats->number_rental_agreement_clauses_type_rental)->toBe(2)
        ->and($rentalAgreement->clauses->first()->asset)->toBeInstanceOf(Asset::class)
        ->and($rentalAgreement->clauses->last()->asset)->toBeInstanceOf(Asset::class)
        ->and($rentalAgreement->stats->number_rental_agreement_snapshots)->toBe(1);

    return $rentalAgreement;
})->depends('create fulfilment customer');

test('update rental agreement', function (RentalAgreement $rentalAgreement) {
    UpdateRentalAgreement::make()->action(
        $rentalAgreement,
        [
            'billing_cycle' => RentalAgreementBillingCycleEnum::WEEKLY,
            'pallets_limit' => 10,
            'state'         => RentalAgreementStateEnum::ACTIVE
        ]
    );
    $rentalAgreement->refresh();
    expect($rentalAgreement->billing_cycle)->toBe(RentalAgreementBillingCycleEnum::WEEKLY)
        ->and($rentalAgreement->pallets_limit)->toBe(10)
        ->and($rentalAgreement->state)->toBe(RentalAgreementStateEnum::DRAFT)
        ->and($rentalAgreement->stats->number_rental_agreement_snapshots)->toBe(2);


    return $rentalAgreement;
})->depends('create rental agreement');

test('update rental agreement cause', function (RentalAgreement $rentalAgreement) {
    $rentalAgreement = UpdateRentalAgreement::make()->action(
        $rentalAgreement,
        [
            'clauses' => [
                'rentals' => [
                    [
                        'asset_id'       => $rentalAgreement->fulfilmentCustomer->fulfilment->rentals->first()->asset_id,
                        'percentage_off' => 30,
                    ],
                    [
                        'asset_id'       => $rentalAgreement->fulfilmentCustomer->fulfilment->rentals->last()->asset_id,
                        'percentage_off' => 50,
                    ],
                ]
            ]
        ]
    );
    $rentalAgreement->refresh();
    expect($rentalAgreement->stats->number_rental_agreement_clauses)->toBe(2)
        ->and($rentalAgreement->stats->number_rental_agreement_clauses_type_rental)->toBe(2)
        ->and($rentalAgreement->clauses->first()->percentage_off)->toEqualWithDelta(30, .001)
        ->and($rentalAgreement->clauses->last()->percentage_off)->toEqualWithDelta(50, .001)
        ->and($rentalAgreement->stats->number_rental_agreement_snapshots)->toBe(3);


    return $rentalAgreement;
})->depends('create rental agreement');


test('Fetch new webhook fulfilment customer', function (FulfilmentCustomer $fulfilmentCustomer) {
    $webhook = FetchNewWebhookFulfilmentCustomer::make()->action(
        $this->organisation,
        $fulfilmentCustomer->fulfilment,
        $fulfilmentCustomer,
        []
    );

    expect($webhook)->toHaveKey('webhook_access_key')
        ->and($webhook['webhook_access_key'])->toBeString()
        ->and(strlen($webhook['webhook_access_key']))->toBe(64);
    $updatedFulfilmentCustomer = FulfilmentCustomer::find($fulfilmentCustomer->id);
    expect($updatedFulfilmentCustomer->webhook_access_key)->toBe($webhook['webhook_access_key']);

    return $webhook;
})->depends('create fulfilment customer');


test('create pallet delivery', function ($fulfilmentCustomer) {
    SendPalletDeliveryNotification::shouldRun()
        ->andReturn();

    $palletDelivery = StorePalletDelivery::make()->action(
        $fulfilmentCustomer,
        [
            'warehouse_id' => $this->warehouse->id,
        ]
    );
    $fulfilmentCustomer->refresh();
    expect($palletDelivery)->toBeInstanceOf(PalletDelivery::class)
        ->and($palletDelivery->state)->toBe(PalletDeliveryStateEnum::IN_PROCESS)
        ->and($palletDelivery->number_pallets)->toBe(0)
        ->and($fulfilmentCustomer->number_pallet_deliveries)->toBe(1)
        ->and($fulfilmentCustomer->number_pallets)->toBe(0);

    return $palletDelivery;
})->depends('create fulfilment customer');

test('update pallet delivery notes', function (PalletDelivery $palletDelivery) {
    UpdatePalletDelivery::make()->action(
        $palletDelivery,
        [
            'customer_notes' => 'Note A',
            'public_notes'   => 'Note B',
            'internal_notes' => 'Note C',

        ]
    );

    expect($palletDelivery->customer_notes)->toBe('Note A')
        ->and($palletDelivery->public_notes)->toBe('Note B')
        ->and($palletDelivery->internal_notes)->toBe('Note C');

    UpdatePalletDelivery::make()->action(
        $palletDelivery,
        [
            'customer_notes' => '',

        ]
    );
    expect($palletDelivery->customer_notes)->toBe('');

    return $palletDelivery;
})->depends('create pallet delivery');

test('add pallet to pallet delivery', function (PalletDelivery $palletDelivery) {
    $pallet = StorePalletFromDelivery::make()->action(
        $palletDelivery,
        [
            'customer_reference' => 'C00001',
            'type'               => PalletTypeEnum::OVERSIZE->value,
            'notes'              => 'note A',
        ]
    );

    $palletDelivery->refresh();
    expect($pallet)->toBeInstanceOf(Pallet::class)
        ->and($pallet->state)->toBe(PalletStateEnum::IN_PROCESS)
        ->and($pallet->status)->toBe(PalletStatusEnum::IN_PROCESS)
        ->and($pallet->type)->toBe(PalletTypeEnum::OVERSIZE)
        ->and($pallet->notes)->toBe('note A')
        ->and($pallet->source_id)->toBeNull()
        ->and($pallet->customer_reference)->toBeString()
        ->and($pallet->received_at)->toBeNull()
        ->and($pallet->fulfilmentCustomer)->toBeInstanceOf(FulfilmentCustomer::class)
        ->and($pallet->fulfilmentCustomer->number_pallets)->toBe(1)
        ->and($pallet->fulfilmentCustomer->number_stored_items)->toBe(0)
        ->and($palletDelivery->number_pallets)->toBe(1)
        ->and($palletDelivery->stats->number_pallets_type_oversize)->toBe(1);


    return $pallet;
})->depends('create pallet delivery');

test('add multiple pallets to pallet delivery', function (PalletDelivery $palletDelivery) {
    StoreMultiplePalletsFromDelivery::make()->action(
        $palletDelivery,
        [
            'warehouse_id'   => $this->warehouse->id,
            'number_pallets' => 3,
            'type'           => PalletTypeEnum::PALLET->value,
        ]
    );

    $palletDelivery->refresh();

    expect($palletDelivery->number_pallets)->toBe(4)
        ->and($palletDelivery->stats->number_pallets_type_pallet)->toBe(3)
        ->and($palletDelivery->stats->number_pallets_type_oversize)->toBe(1)
        ->and($palletDelivery->number_pallet_stored_items)->toBe(0)
        ->and($palletDelivery->number_stored_items)->toBe(0);

    return $palletDelivery;
})->depends('create pallet delivery');

test('remove a pallet from pallet delivery', function (PalletDelivery $palletDelivery) {
    DeletePalletInDelivery::make()->action(
        $palletDelivery->pallets->last()
    );

    $palletDelivery->refresh();

    expect($palletDelivery->number_pallets)->toBe(3)
        ->and($palletDelivery->stats->number_pallets_type_pallet)->toBe(2)
        ->and($palletDelivery->stats->number_pallets_type_oversize)->toBe(1)
        ->and($palletDelivery->number_pallet_stored_items)->toBe(0)
        ->and($palletDelivery->number_stored_items)->toBe(0);

    return $palletDelivery;
})->depends('add multiple pallets to pallet delivery');

test('confirm pallet delivery', function (PalletDelivery $palletDelivery) {
    SendPalletDeliveryNotification::shouldRun()->andReturn();

    $palletDelivery = ConfirmPalletDelivery::make()->action($palletDelivery);

    $pallet = $palletDelivery->pallets->first();

    expect($palletDelivery->state)->toBe(PalletDeliveryStateEnum::CONFIRMED)
        ->and($palletDelivery->confirmed_at)->toBeInstanceOf(Carbon::class)
        ->and($palletDelivery->number_pallets)->toBe(3)
        ->and($palletDelivery->number_pallet_stored_items)->toBe(0)
        ->and($palletDelivery->number_stored_items)->toBe(0)
        // ->and($pallet->reference)->toEndWith('-p0001')
        ->and($pallet->state)->toBe(PalletStateEnum::CONFIRMED)
        ->and($pallet->status)->toBe(PalletStatusEnum::RECEIVING);

    return $palletDelivery;
})->depends('add multiple pallets to pallet delivery');

test('receive pallet delivery', function (PalletDelivery $palletDelivery) {
    SendPalletDeliveryNotification::shouldRun()->andReturn();

    $palletDelivery = ReceivedPalletDelivery::make()->action($palletDelivery);

    $palletDelivery->refresh();

    $palletNotInRentalCount = $palletDelivery->pallets()->whereNull('rental_id')->count();


    expect($palletDelivery->state)->toBe(PalletDeliveryStateEnum::RECEIVED)
        ->and($palletDelivery->received_at)->toBeInstanceOf(Carbon::class)
        ->and($palletDelivery->number_pallets)->toBe(3)
        ->and($palletNotInRentalCount)->toBe(1);

    return $palletDelivery;
})->depends('confirm pallet delivery');

test('start booking-in pallet delivery', function (PalletDelivery $palletDelivery) {
    $palletDelivery = StartBookingPalletDelivery::make()->action($palletDelivery);

    $palletDelivery->refresh();


    expect($palletDelivery->state)->toBe(PalletDeliveryStateEnum::BOOKING_IN)
        ->and($palletDelivery->booking_in_at)->toBeInstanceOf(Carbon::class);

    return $palletDelivery;
})->depends('receive pallet delivery');

test('set location of first pallet in the pallet delivery', function (PalletDelivery $palletDelivery) {
    $pallet = $palletDelivery->pallets->first();
    /** @var Location $location */
    $location = $this->warehouse->locations()->first();

    BookInPallet::make()->action($pallet, ['location_id' => $location->id]);
    $pallet->refresh();
    expect($pallet->location)->toBeInstanceOf(Location::class)
        ->and($pallet->location->id)->toBe($location->id)
        ->and($pallet->received_at)->toBeInstanceOf(Carbon::class)
        ->and($pallet->booked_in_at)->toBeInstanceOf(Carbon::class)
        ->and($pallet->set_as_not_received_at)->toBeNull()
        ->and($pallet->state)->toBe(PalletStateEnum::BOOKED_IN)
        ->and($pallet->status)->toBe(PalletStatusEnum::RECEIVING);

    return $palletDelivery;
})->depends('start booking-in pallet delivery');

test('set rental to first pallet in the pallet delivery', function (PalletDelivery $palletDelivery) {
    $pallet = $palletDelivery->pallets->first();
    $rental = $palletDelivery->fulfilment->rentals->last();
    expect($rental)->toBeInstanceOf(Rental::class);

    SetPalletRental::make()->action($pallet, ['rental_id' => $rental->id]);
    $pallet->refresh();
    $palletNotInRentalCount = $palletDelivery->pallets()->whereNull('rental_id')->count();

    expect($pallet->rental)->toBeInstanceOf(Rental::class)
        ->and($palletNotInRentalCount)->toBe(0);


    return $palletDelivery;
})->depends('set location of first pallet in the pallet delivery');

test('can create pallet delivery pdf', function (PalletDelivery $palletDelivery) {
    $pdf = PdfPalletDelivery::run($palletDelivery);
    expect($pdf->output())->toBeString();

    return $palletDelivery;
})->depends('set rental to first pallet in the pallet delivery');


test('set second pallet in the pallet delivery as not delivered', function (PalletDelivery $palletDelivery) {
    $pallet = $palletDelivery->pallets->skip(1)->first();

    SetPalletAsNotReceived::make()->action($pallet);
    $pallet->refresh();
    expect($pallet->state)->toBe(PalletStateEnum::NOT_RECEIVED)
        ->and($pallet->set_as_not_received_at)->toBeInstanceOf(Carbon::class)
        ->and($pallet->booked_in_at)->toBeNull()
        ->and($pallet->status)->toBe(PalletStatusEnum::NOT_RECEIVED);

    return $palletDelivery;
})->depends('set location of first pallet in the pallet delivery');


test('create pallet delivery that was not delivered by marking items', function ($fulfilmentCustomer) {
    SendPalletDeliveryNotification::shouldRun()
        ->andReturn();

    $palletDelivery = StorePalletDelivery::make()->action($fulfilmentCustomer, ['warehouse_id' => $this->warehouse->id,]);
    $pallet         = StorePalletFromDelivery::make()->action($palletDelivery, []);
    $palletDelivery = ConfirmPalletDelivery::make()->action($palletDelivery);
    $palletDelivery = ReceivedPalletDelivery::make()->action($palletDelivery);

    expect($palletDelivery->stats->number_pallets)->toBe(1)
        ->and($palletDelivery->state)->toBe(PalletDeliveryStateEnum::RECEIVED);
    SetPalletAsNotReceived::make()->action($pallet);
    $palletDelivery->refresh();
    expect($palletDelivery->state)->toBe(PalletDeliveryStateEnum::NOT_RECEIVED);

    return $palletDelivery;
})->depends('create fulfilment customer');


test('set location of third pallet in the pallet delivery', function (PalletDelivery $palletDelivery) {
    $pallet = $palletDelivery->pallets->last();
    /** @var Location $location */
    $location = $this->warehouse->locations->last();
    expect($location->stats->number_pallets)->toBe(0)
        ->and($palletDelivery->state)->toBe(PalletDeliveryStateEnum::BOOKING_IN);

    BookInPallet::make()->action($pallet, ['location_id' => $location->id]);
    $pallet->refresh();
    $location->refresh();

    $palletStateBookedInCount    = $palletDelivery->pallets()->where('state', PalletStateEnum::BOOKED_IN)->count();
    $palletStateNotReceivedCount = $palletDelivery->pallets()->where('state', PalletStateEnum::NOT_RECEIVED)->count();
    $palletStateReceivedCount    = $palletDelivery->pallets()->where('state', PalletStateEnum::RECEIVED)->count();
    $palletReceivedCount         = $palletStateReceivedCount + $palletStateNotReceivedCount + $palletStateBookedInCount;
    $palletNotInRentalCount      = $palletDelivery->pallets()
        ->where('state', '!=', PalletStateEnum::NOT_RECEIVED)->whereNull('rental_id')->count();

    $palletDelivery->refresh();

    expect($pallet->location)->toBeInstanceOf(Location::class)
        ->and($palletReceivedCount)->toBe(3)
        ->and($palletStateNotReceivedCount)->toBe(1)
        ->and($palletNotInRentalCount)->toBe(0)
        ->and($pallet->location->id)->toBe($location->id)
        ->and($pallet->state)->toBe(PalletStateEnum::BOOKED_IN)
        ->and($pallet->status)->toBe(PalletStatusEnum::RECEIVING)
        ->and($location->stats->number_pallets)->toBe(1)
        ->and($palletDelivery->state)->toBe(PalletDeliveryStateEnum::BOOKING_IN);

    return $palletDelivery;
})->depends('set second pallet in the pallet delivery as not delivered');


test('set pallet delivery as booked in', function (PalletDelivery $palletDelivery) {
    SendPalletDeliveryNotification::shouldRun()->andReturn();

    $fulfilmentCustomer = $palletDelivery->fulfilmentCustomer;
    expect($fulfilmentCustomer->currentRecurringBill)->toBeNull()
        ->and($palletDelivery->state)->toBe(PalletDeliveryStateEnum::BOOKING_IN);

    $palletDelivery = SetPalletDeliveryAsBookedIn::make()->action($palletDelivery);
    $palletDelivery->refresh();
    $fulfilmentCustomer->refresh();


    expect($palletDelivery->state)->toBe(PalletDeliveryStateEnum::BOOKED_IN)
        ->and($palletDelivery->booked_in_at)->toBeInstanceOf(Carbon::class)
        ->and($palletDelivery->number_pallets)->toBe(3)
        ->and($palletDelivery->number_pallet_stored_items)->toBe(0)
        ->and($palletDelivery->number_stored_items)->toBe(0)
        ->and($palletDelivery->group->fulfilmentStats->number_recurring_bills)->toBe(1)
        ->and($palletDelivery->group->fulfilmentStats->number_recurring_bills_status_former)->toBe(0)
        ->and($palletDelivery->group->fulfilmentStats->number_recurring_bills_status_current)->toBe(1)
        ->and($palletDelivery->organisation->fulfilmentStats->number_recurring_bills)->toBe(1)
        ->and($palletDelivery->organisation->fulfilmentStats->number_recurring_bills_status_former)->toBe(0)
        ->and($palletDelivery->organisation->fulfilmentStats->number_recurring_bills_status_current)->toBe(1)
        ->and($palletDelivery->fulfilment->stats->number_recurring_bills)->toBe(1)
        ->and($palletDelivery->fulfilment->stats->number_recurring_bills_status_former)->toBe(0)
        ->and($palletDelivery->fulfilment->stats->number_recurring_bills_status_current)->toBe(1)
        ->and($palletDelivery->fulfilmentCustomer->number_recurring_bills)->toBe(1)
        ->and($palletDelivery->fulfilmentCustomer->number_recurring_bills_status_former)->toBe(0)
        ->and($palletDelivery->fulfilmentCustomer->number_recurring_bills_status_current)->toBe(1)
        ->and($fulfilmentCustomer->currentRecurringBill)->toBeInstanceOf(RecurringBill::class);

    $recurringBill = $fulfilmentCustomer->currentRecurringBill;
    expect($recurringBill->stats->number_transactions)->toBe(2)
        ->and($recurringBill->stats->number_transactions_type_pallets)->toBe(2)
        ->and($recurringBill->stats->number_transactions_type_stored_items)->toBe(0);

    $firstPallet  = $palletDelivery->pallets->first();
    $secondPallet = $palletDelivery->pallets->skip(1)->first();
    $thirdPallet  = $palletDelivery->pallets->last();

    expect($firstPallet->state)->toBe(PalletStateEnum::NOT_RECEIVED)
        ->and($secondPallet->state)->toBe(PalletStateEnum::STORING)
        ->and($secondPallet->storing_at)->toBeInstanceOf(Carbon::class)
        ->and($secondPallet->currentRecurringBill)->toBeInstanceOf(RecurringBill::class)
        ->and($thirdPallet->state)->toBe(PalletStateEnum::STORING);


    return $palletDelivery;
})->depends('set location of third pallet in the pallet delivery');


test('create pallet return', function (PalletDelivery $palletDelivery) {
    SendPalletReturnNotification::shouldRun()
        ->andReturn();


    $fulfilmentCustomer = $palletDelivery->fulfilmentCustomer;

    $palletReturn = StorePalletReturn::make()->action(
        $fulfilmentCustomer,
        [
            'warehouse_id' => $this->warehouse->id,
        ]
    );
    $fulfilmentCustomer->refresh();
    expect($palletReturn)->toBeInstanceOf(PalletReturn::class)
        ->and($palletReturn->state)->toBe(PalletReturnStateEnum::IN_PROCESS)
        ->and($palletReturn->number_pallets)->toBe(0)
        ->and($fulfilmentCustomer->fulfilment->stats->number_pallet_returns)->toBe(1)
        ->and($fulfilmentCustomer->number_pallet_returns)->toBe(1)
        ->and($fulfilmentCustomer->number_pallet_returns_state_in_process)->toBe(1);

    return $palletReturn;
})->depends('set pallet delivery as booked in');

test('create pallet no delivery', function (Fulfilment $fulfilment) {
    $customer = StoreCustomer::make()->action(
        $fulfilment->shop,
        Customer::factory()->definition(),
    );


    $pallet = StorePallet::make()->action(
        $customer->fulfilmentCustomer,
        array_merge([
            'warehouse_id' => $this->warehouse->id,
        ], Pallet::factory()->definition())
    );


    expect($pallet)->toBeInstanceOf(Pallet::class)
        ->and($pallet->state)->toBe(PalletStateEnum::IN_PROCESS)
        ->and($pallet->status)->toBe(PalletStatusEnum::IN_PROCESS)
        ->and($pallet->type)->toBe(PalletTypeEnum::PALLET)
        ->and($pallet->notes)->toBe('')
        ->and($pallet->source_id)->toBeNull()
        ->and($pallet->customer_reference)->toBeString()
        ->and($pallet->received_at)->toBeNull()
        ->and($pallet->fulfilmentCustomer)->toBeInstanceOf(FulfilmentCustomer::class)
        ->and($pallet->fulfilmentCustomer->number_pallets)->toBe(1)
        ->and($pallet->fulfilmentCustomer->number_stored_items)->toBe(0);

    return $pallet;
})->depends('create fulfilment shop');

test('update pallet', function (Pallet $pallet) {

    $updatedPallet = UpdatePallet::make()->action(
        $pallet,
        [
            'state' => PalletStateEnum::DAMAGED,
            'status' => PalletStatusEnum::INCIDENT,
            'notes'  => 'sorry'
        ]
    );


    expect($updatedPallet)->toBeInstanceOf(Pallet::class)
        ->and($updatedPallet->state)->toBe(PalletStateEnum::DAMAGED)
        ->and($updatedPallet->status)->toBe(PalletStatusEnum::INCIDENT)
        ->and($updatedPallet->notes)->toBe('sorry');
        
    return $updatedPallet;
})->depends('create pallet no delivery');

test('hydrate fulfilment command', function () {
    $this->artisan('hydrate:fulfilments '.$this->organisation->slug)->assertExitCode(0);
});

test('hydrate fulfilment customer command', function () {
    $this->artisan('hydrate:fulfilment-customers '.$this->organisation->slug)->assertExitCode(0);
});

test('hydrate pallet delivery command', function () {
    $this->artisan('hydrate:pallet-deliveries  '.$this->organisation->slug)->assertExitCode(0);
});

test('hydrate rental agreements command', function () {
    $this->artisan('hydrate:rental-agreements  '.$this->organisation->slug)->assertExitCode(0);
});
