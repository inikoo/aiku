<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jul 2023 14:57:55 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Firebase\StoreFirebaseCloudMessagingToken;

Route::get('/ping', function () {
    return 'pong';
})->name('ping');

Route::middleware('auth')->post('/firebase/token', StoreFirebaseCloudMessagingToken::class);
