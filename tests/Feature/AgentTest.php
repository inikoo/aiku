<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 08 May 2023 09:03:42 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Procurement\Agent\ChangeAgentOwner;
use App\Actions\Procurement\Agent\StoreAgent;
use App\Actions\Procurement\Agent\UpdateAgent;
use App\Actions\Procurement\Agent\UpdateAgentVisibility;
use App\Models\Helpers\Address;
use App\Models\Procurement\Agent;
use App\Models\Tenancy\Tenant;
use Illuminate\Validation\ValidationException;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();
});

test('create agent', function () {
    $agent = StoreAgent::make()->action(app('currentTenant'), Agent::factory()->definition(), Address::factory()->definition());
    $this->assertModelExists($agent);
    return $agent;
});

test('number of agents should be one', function () {
    $this->assertEquals(1, app('currentTenant')->procurementStats->number_agents);
})->depends('create agent');

test('create another agent', function () {
    $agent = StoreAgent::make()->action(app('currentTenant'), Agent::factory()->definition(), Address::factory()->definition());
    $this->assertModelExists($agent);
});

test('number of agents should be two', function () {
    $this->assertEquals(2, app('currentTenant')->procurementStats->number_agents);
})->depends('create agent', 'create another agent');

test('check if agent match with tenant', function ($agent) {
    $agent = $agent->where('owner_id', app('currentTenant')->id)->first();

    $this->assertModelExists($agent);
})->depends('create agent');

test('check if agent not match with tenant', function ($agent) {
    $tenant2 = Tenant::where('slug', 'aus')->first();
    $tenant2->makeCurrent();

    $agent = $agent->where('owner_id', app('currentTenant')->id)->first();

    expect($agent)->toBeNull();
})->depends('create agent');

test('cant change agent visibility to private', function ($agent) {
    expect(function () use ($agent) {
        UpdateAgentVisibility::make()->action($agent, false);
    })->toThrow(ValidationException::class);
})->depends('create agent');

test('change agent visibility to public', function ($agent) {
    $agent = UpdateAgentVisibility::make()->action($agent->first(), false);

    $this->assertModelExists($agent);
})->depends('create agent');

test('change agent owner', function ($agent) {
    $agent = ChangeAgentOwner::run($agent, app('currentTenant'));

    $this->assertModelExists($agent);
})->depends('create agent');

test('check if last tenant cant update', function ($agent) {
    $tenant2 = Tenant::where('slug', 'aus')->first();
    $tenant2->makeCurrent();

    expect(function () use ($agent) {
        UpdateAgent::make()->action($agent, Agent::factory()->definition());
    })->toThrow(ValidationException::class);
})->depends('create agent');
