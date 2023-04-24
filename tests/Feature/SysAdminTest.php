<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 21:22:24 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\SysAdmin\Admin\StoreAdmin;
use App\Actions\SysAdmin\SysUser\StoreSysUser;
use App\Models\SysAdmin\Admin;
use App\Models\SysAdmin\SysUser;

beforeAll(fn () => loadDB('fresh_with_assets.dump'));


test('create a system admin', function () {
    $admin = StoreAdmin::make()->asAction(Admin::factory()->definition());
    $this->assertModelExists($admin);
});

test('create a system admin user', function () {
    $admin   = StoreAdmin::make()->asAction(Admin::factory()->definition());
    $sysUser = StoreSysUser::make()->asAction($admin, SysUser::factory()->definition());
    $this->assertModelExists($sysUser);
});
