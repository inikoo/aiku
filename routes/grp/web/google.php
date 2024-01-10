<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 09 Sept 2022 18:32:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Google\Drive\AuthorizeClientGoogleDrive;
use App\Actions\Google\Drive\CallbackClientGoogleDrive;

Route::as("drive.")->group(function () {
    Route::get("authorize", AuthorizeClientGoogleDrive::class)->name('authorize');
    Route::get("callback", CallbackClientGoogleDrive::class)->name('callback');
});
