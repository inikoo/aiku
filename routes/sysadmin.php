<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 21:58:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Actions\SysAdmin\User\IndexUser;
use App\Actions\SysAdmin\User\ShowUser;


Route::get('/users', IndexUser::class)->name('users.index');
Route::get('/users/{user}', ShowUser::class)->name('users.show');
