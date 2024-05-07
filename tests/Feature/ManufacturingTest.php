<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 14:38:07 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Manufacturing\Production\StoreProduction;
use App\Actions\Manufacturing\Production\UpdateProduction;
use App\Models\Manufacturing\Production;
use Illuminate\Validation\ValidationException;

beforeAll(function () {
    loadDB('test_base_database.dump');
});

beforeEach(function () {
    $this->organisation = createOrganisation();
    $this->group        = group();
    $this->guest        = createAdminGuest($this->group);
});

test('create production', function () {
    $production = StoreProduction::make()->action(
        $this->organisation,
        [
            'code' => 'ts12',
            'name' => 'testName',
        ]
    );

    expect($production)->toBeInstanceOf(Production::class)
        ->and($this->organisation->manufactureStats->number_productions)->toBe(1)
        ->and($this->organisation->manufactureStats->number_productions_state_in_process)->toBe(1)
        ->and($this->organisation->manufactureStats->number_productions_state_open)->toBe(0)
        ->and($this->organisation->manufactureStats->number_productions_state_closing_down)->toBe(0)
        ->and($this->organisation->manufactureStats->number_productions_state_closed)->toBe(0)
        ->and($this->organisation->group->manufactureStats->number_productions)->toBe(1)
        ->and($this->organisation->group->manufactureStats->number_productions_state_in_process)->toBe(1)
        ->and($this->organisation->group->manufactureStats->number_productions_state_open)->toBe(0)
        ->and($this->guest->user->authorisedProductions()->where('organisation_id', $this->organisation->id)->count())->toBe(1)
        ->and($this->guest->user->number_authorised_productions)->toBe(1)
        ->and($this->guest->user->hasPermissionTo("productions.$production->id"))->toBeTrue();


    return $production;
});

test('production cannot be created with same code', function () {
    StoreProduction::make()->action(
        $this->organisation,
        [
            'code' => 'ts12',
            'name' => 'testName',
        ]
    );
})->depends('create production')->throws(ValidationException::class);

test('production cannot be created with same code case is sensitive', function () {
    StoreProduction::make()->action(
        $this->organisation,
        [
            'code' => 'TS12',
            'name' => 'testName',
        ]
    );
})->depends('create production')->throws(ValidationException::class);

test('update production', function ($production) {
    $production = UpdateProduction::make()->action($production, ['name' => 'Pika Ltd']);
    expect($production->name)->toBe('Pika Ltd');
})->depends('create production');

test('create production by command', function () {
    $this->artisan('production:create', [
        'organisation' => $this->organisation->slug,
        'code'         => 'AA',
        'name'         => 'testName A',
    ])->assertExitCode(0);

    $production = Production::where('code', 'AA')->first();

    $organisation = $this->organisation;
    $organisation->refresh();


    expect($organisation->manufactureStats->number_productions)->toBe(2)
        ->and($organisation->group->manufactureStats->number_productions)->toBe(2)
        ->and($production->roles()->count())->toBe(1);
});

test('seed production permissions', function () {
    setPermissionsTeamId($this->group->id);
    $this->artisan('production:seed-permissions')->assertExitCode(0);
    $production = Production::where('code', 'AA')->first();
    expect($production->roles()->count())->toBe(1);
});
