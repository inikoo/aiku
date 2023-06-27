<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 27 Jun 2023 14:15:16 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Auth\GroupUser\UI\IndexGroupUsersOtherTenants;
use Illuminate\Support\Facades\Route;

Route::get('/group-users-other-tenants', IndexGroupUsersOtherTenants::class)->name('group-users.index');
