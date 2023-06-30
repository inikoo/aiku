<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 30 Jun 2023 14:07:23 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Firebase\StoreFirebaseCloudMessagingToken;

Route::post('token', StoreFirebaseCloudMessagingToken::class)->name('fcmToken');
