<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Sept 2024 12:01:24 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\SysAdmin\User\UpdateUserGroupPseudoJobPositions;
use App\Actions\SysAdmin\User\UpdateUserOrganisationPseudoJobPositions;
use App\Actions\SysAdmin\User\UpdateUser;
use Illuminate\Support\Facades\Route;

Route::name('user.')->prefix('user/{user:id}')->group(function () {
    Route::patch('', UpdateUser::class)->name('update');
    Route::patch('group-permissions', UpdateUserGroupPseudoJobPositions::class)->name('group_permissions.update');
    Route::patch('organisation-pseudo-job-positions/{organisation:id}', UpdateUserOrganisationPseudoJobPositions::class)->name('organisation_pseudo_job_positions.update')->withoutScopedBindings();
});
