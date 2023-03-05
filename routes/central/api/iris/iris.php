<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 27 Nov 2022 12:48:44 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

Route::get('/ping', function () {
    return 'pong';
})->name('ping');

Route::prefix('fulfilment')
    ->name('fulfilment.')
    ->group(__DIR__.'/fulfilment.php');

Route::prefix('dropshipping')
    ->name('dropshipping.')
    ->group(__DIR__.'/dropshipping.php');
