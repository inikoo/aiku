<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Jan 2024 17:31:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\HumanResources\Workplace\StoreWorkplace;
use App\Actions\UI\Profile\GetProfileAppLoginQRCode;
use App\Enums\HumanResources\ClockingMachine\ClockingMachineTypeEnum;
use App\Enums\HumanResources\Workplace\WorkplaceTypeEnum;
use App\Models\Assets\Timezone;
use App\Models\Helpers\Address;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\{actingAs};

use function Pest\Laravel\{getJson};
use function Pest\Laravel\{postJson};

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->adminGuest   = createAdminGuest($this->organisation->group);
    $this->user         = $this->adminGuest->user;

    Config::set(
        'inertia.testing.page_paths',
        [resource_path('js/Pages/Grp')]
    );
    $this->qrCode = Str::ulid()->toBase32();


    if (!Workplace::where('name', 'office')->exists()) {
        $modelData = [
            'name'       => 'office',
            'type'       => WorkplaceTypeEnum::BRANCH,
            'address'    => Address::factory()->definition(),
            'timezone_id'=> Timezone::where('name', 'Asia/Kuala_Lumpur')->first()->id
        ];

        StoreWorkplace::make()->action($this->organisation, $modelData);
    }

    $this->workplace = Workplace::where('name', 'office')->first();
});


test('create qr code', function () {
    actingAs($this->user);

    GetProfileAppLoginQRCode::partialMock()
        ->shouldReceive('getCode')
        ->andReturn($this->qrCode);

    $response = getJson(route('grp.models.profile.app-login-qrcode'));
    $response->assertOk();
    $response->assertJson([
        'code' => $this->qrCode
    ]);
});

test('create api token from qr code', function () {
    Cache::shouldReceive('get')
        ->once()
        ->with('profile-app-qr-code:'.$this->qrCode)
        ->andReturn($this->user->id);
    Cache::shouldReceive('forget')
        ->once()
        ->with('profile-app-qr-code:'.$this->qrCode);


    $response = postJson(route('api.mobile-app.tokens.qr-code.store', [
        'code'        => $this->qrCode,
        'device_name' => 'test device'
    ]));


    $response->assertOk();
    $response->assertJsonStructure([
        'token'
    ]);
})->depends('create qr code');

test('create api token from credentials', function () {
    $response = postJson(route('api.mobile-app.tokens.credentials.store', [
        'username'    => $this->user->username,
        'password'    => 'password',
        'device_name' => 'test device'
    ]));
    $response->assertOk();
    $response->assertJsonStructure([
        'token'
    ]);
});

test('get profile data', function () {
    Sanctum::actingAs(
        $this->user,
        ['*']
    );
    $response = getJson(route('api.profile.show'));
    $response->assertOk();
    expect($response->json('data'))->toBeArray()
        ->and($response->json('data'))
        ->id->toBe($this->user->id)
        ->contact_name->toBe($this->user->contact_name)
        ->username->toBe($this->user->username)
        ->roles->toBeArray()
        ->permissions->toBeArray();
});

test('get clocking machines list (empty)', function () {
    Sanctum::actingAs(
        $this->user,
        ['*']
    );

    $response = getJson(route('api.org.hr.clocking-machines.index', $this->organisation->id));

    $response->assertOk();
    expect($response->json('data'))->toBeArray()
        ->and($response->json('data'))
        ->toHaveCount(0);
});


test('get working places list', function () {
    Sanctum::actingAs(
        $this->user,
        ['*']
    );
    $response = getJson(route('api.org.hr.workplaces.index', $this->organisation->id));

    $response->assertOk();
    expect($response->json('data'))->toBeArray()
        ->and($response->json('data'))
        ->toHaveCount(1);
});

test('get workplace data', function () {
    Sanctum::actingAs(
        $this->user,
        ['*']
    );
    $response = getJson(route('api.org.hr.workplaces.show', [
        $this->organisation->id,
        $this->workplace->id
    ]));
    $response->assertOk();

    expect($response->json('data'))->toBeArray()
        ->and($response->json('data'))
        ->id->toBe($this->workplace->id)
        ->name->toBe($this->workplace->name);
});

test('get clocking machines list in workplace', function () {
    Sanctum::actingAs(
        $this->user,
        ['*']
    );
    $response = getJson(
        route(
            'api.org.hr.workplaces.show.clocking-machines.index',
            [
                $this->organisation->id,
                $this->workplace->id
            ]
        )
    );

    $response->assertOk();
    expect($response->json('data'))->toBeArray()
        ->and($response->json('data'))
        ->toHaveCount(0);
});

test('create clocking machine in workplace', function () {
    Sanctum::actingAs(
        $this->user,
        ['*']
    );
    $response = postJson(route('api.org.hr.workplaces.show.clocking-machines.store', [
        $this->organisation,
        $this->workplace->id
    ]), [
        'name'    => 'test clocking machine',
        'type'    => ClockingMachineTypeEnum::STATIC_NFC->value,
        'nfc_tag' => 'test-nfc-tag'
    ]);

    $response->assertStatus(201);

    expect($response->json('data'))->toBeArray()
        ->and($response->json('data'))
        ->name->toBe('test clocking machine')->nfc_tag->toBe('test-nfc-tag');

    /** @var Organisation $organisation */
    $organisation = $this->organisation;
    $organisation->refresh();
    expect($organisation->humanResourcesStats->number_clocking_machines)->toBe(1)
        ->and($organisation->humanResourcesStats->number_clocking_machines_type_static_nfc)->toBe(1);

    /** @var Workplace $workplace */
    $workplace = $this->workplace;
    $workplace->refresh();
    expect($workplace->stats->number_clocking_machines)->toBe(1)
        ->and($workplace->stats->number_clocking_machines_type_static_nfc)->toBe(1);
});

test('get clocking machines list in working place', function () {
    Sanctum::actingAs(
        $this->user,
        ['*']
    );
    $response = getJson(
        route(
            'api.org.hr.workplaces.show.clocking-machines.index',
            [
                $this->organisation->id,
                $this->workplace->id
            ]
        )
    );

    $response->assertOk();
    expect($response->json('data'))->toBeArray()
        ->and($response->json('data'))
        ->toHaveCount(1);
});
