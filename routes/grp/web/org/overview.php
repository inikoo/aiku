<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

use App\Actions\Overview\ShowOrganisationOverviewHub;
use App\Actions\SysAdmin\Organisation\UI\IndexHistoryInOrganisation;

Route::get('/', ShowOrganisationOverviewHub::class)->name('hub');

Route::name('changelog.')->prefix('changelog')->group(function () {
    Route::get('/', IndexHistoryInOrganisation::class)->name('index');
});
