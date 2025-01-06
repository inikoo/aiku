<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Sept 2024 12:01:24 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\SysAdmin\User\UpdateUsersPseudoJobPositions;
use App\Actions\SysAdmin\User\UpdateUser;
use Illuminate\Support\Facades\Route;

Route::name('user.')->prefix('user/{user:id}')->group(function () {
    Route::patch('', UpdateUser::class)->name('update');
    Route::patch('positions', UpdateUsersPseudoJobPositions::class)->name('pseudo-job-positions.update')->withoutScopedBindings();
});
