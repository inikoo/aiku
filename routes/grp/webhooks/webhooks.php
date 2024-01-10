<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Jan 2024 12:27:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Mail\Notifications\GetSnsNotification;

Route::name('webhooks.')->group(function () {
    Route::any('sns', GetSnsNotification::class)->name('sns');
});
