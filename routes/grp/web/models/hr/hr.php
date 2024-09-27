<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 29 Aug 2024 00:18:41 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\HumanResources\Employee\ImportEmployees;
use Illuminate\Support\Facades\Route;

Route::name('employees.')->prefix('employees')->group(function () {
    Route::post('{organisation:id}/import', ImportEmployees::class)->name('import');
});
