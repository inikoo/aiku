<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:04:32 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Models\SysAdmin\Admin;
use App\Models\SysAdmin\SysUser;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(
        SysUser::factory()->for(Admin::factory(), 'userable')->create(),
        ['*'],
        'api-admin-user'
    );
});

test('create first deployment', function () {
    $response = $this->post(route('deployments.store'));
    $response->assertCreated();
});

test('get latest deployment', function () {
    $response = $this->get(route('deployments.latest'));
    $response->assertOK();
});

test('show deployment', function () {
    $response = $this->get(route('deployments.show', 1));
    $response->assertOK();
});

test('edit deployment', function () {

    $response = $this->patch(route(
        'deployments.latest.edit',
        [
            "version" => "0.1.1",
            "hash"    => "4019599a",
            "state"   => "deployed"
        ],
    ));
    $response->assertOK();
});

test('create backup files', function (){
    $fileName = "backup_from_test";
    $ext=".zip";
    $path = env("DB_BACKUP_DIR", "storage/backups/aiku/");
    if(\File::exists($path.$fileName.$ext)){
        \File::delete($path.$fileName.$ext);
    }
    $response = $this->artisan('backup:new -N '. $fileName);
    $response->assertOK();
});
