<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 14:38:07 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Manufacturing\Hydrators\GroupHydrateManufacture;
use App\Actions\Manufacturing\Hydrators\OrganisationHydrateManufacture;
use App\Actions\Manufacturing\Production\StoreProduction;
use App\Actions\Manufacturing\Production\UpdateProduction;
use App\Actions\Manufacturing\RawMaterial\StoreRawMaterial;
use App\Actions\Manufacturing\RawMaterial\UpdateRawMaterial;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStateEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStockStatusEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialUnitEnum;
use App\Models\Manufacturing\Production;
use App\Models\Manufacturing\RawMaterial;
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

test('can store a raw material', function () {
    $data = [
        'key'                          => 1,
        'type'                         => RawMaterialTypeEnum::PART,
        'type_key'                     => 2,
        'state'                        => RawMaterialStateEnum::IN_PROCESS,
        'production_supplier_key'      => 3,
        'creation_date'                => now()->toDateString(),
        'code'                         => 'RM001',
        'description'                  => 'Test Raw Material',
        'part_unit_ratio'              => 1.5,
        'unit'                         => RawMaterialUnitEnum::KILOGRAM,
        'unit_label'                   => 'Kilogram',
        'unit_cost'                    => 10.5,
        'stock'                        => 100,
        'stock_status'                 => RawMaterialStockStatusEnum::UNLIMITED,
        'production_parts_number'      => 20,
    ];
    $rawMaterial = StoreRawMaterial::make()->action(
        $this->organisation,
        $data
    );

    // Assertions
    expect($rawMaterial)->toBeInstanceOf(RawMaterial::class);
    expect($rawMaterial->group_id)->toBe($this->organisation->group_id);
    expect($rawMaterial->key)->toBe($data['key']);
    // Add more assertions for other attributes

    return $rawMaterial;
});

test('can update a raw material', function ($rawMaterial) {
    // Create a raw material

    // Prepare data for update
    $data = [
        'key'                          => 2,
        'type'                         => RawMaterialTypeEnum::INTERMEDIATE,
        'type_key'                     => 3,
        'state'                        => RawMaterialStateEnum::DISCONTINUED,
        'production_supplier_key'      => 4,
        'creation_date'                => now()->toDateString(),
        'code'                         => 'RM002',
        'description'                  => 'Updated Raw Material',
        'part_unit_ratio'              => 2.5,
        'unit'                         => RawMaterialUnitEnum::LITER,
        'unit_label'                   => 'Liter',
        'unit_cost'                    => 15.5,
        'stock'                        => 200,
        'stock_status'                 => RawMaterialStockStatusEnum::CRITICAL,
        'production_parts_number'      => 30,
    ];


    $updatedRawMaterial = UpdateRawMaterial::make()->action(
        $rawMaterial,
        $data
    );

    // Assertions
    expect($updatedRawMaterial)->toBeInstanceOf(RawMaterial::class);
    expect($updatedRawMaterial->id)->toBe($rawMaterial->id);
    expect($updatedRawMaterial->key)->toBe($data['key']);
    expect($updatedRawMaterial->type)->toBe($data['type']);
    expect($updatedRawMaterial->state)->toBe($data['state']);
    expect($updatedRawMaterial->unit)->toBe($data['unit']);
    expect($updatedRawMaterial->stock_status)->toBe($data['stock_status']);
    
})->depends('can store a raw material');

test('test group hydrate manufacture', function($production, $rawMaterial) {
    // Create a group
   
    // Hydrate manufacture stats for the group
    GroupHydrateManufacture::make()->handle($production->group);

    // Assertions for manufacture stats
    expect($production->group->manufactureStats->number_productions)->toBe(2);
    expect($rawMaterial->group->manufactureStats->number_raw_materials)->toBe(1);
})->depends('create production', 'can store a raw material');

test('test organisation hydrate manufacture', function($production, $rawMaterial) {
    // Create a group
   
    // Hydrate manufacture stats for the group
    OrganisationHydrateManufacture::make()->handle($production->organisation);

    // Assertions for manufacture stats
    expect($production->organisation->manufactureStats->number_productions)->toBe(2);
    expect($rawMaterial->organisation->manufactureStats->number_raw_materials)->toBe(1);
})->depends('create production', 'can store a raw material');;



