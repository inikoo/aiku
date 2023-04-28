<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Apr 2023 16:05:15 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Tests\Feature;

use App\Actions\Leads\Prospect\StoreProspect;
use App\Actions\Leads\Prospect\UpdateProspect;
use App\Models\Leads\Prospect;
use App\Models\Tenancy\Tenant;

beforeAll(fn () => loadDB('d3_with_tenants.dump'));

beforeEach(function () {
    $tenant = Tenant::where('slug', 'agb')->first();
    $tenant->makeCurrent();

});


test('create prospect', function () {
    $prospect = StoreProspect::make()->action(Prospect::factory()->definition());
    $this->assertModelExists($prospect);
    return $prospect;
});

test('update prospect', function ($prospect) {
    $prospect = UpdateProspect::make()->action($prospect, ['name' => 'Pika']);
    expect($prospect->name)->toBe('Pika');
})->depends('create prospect');
