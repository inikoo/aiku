<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 30 Jun 2023 11:07:48 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Notifications\GetSnsNotification;
use Illuminate\Support\Facades\Route;

Route::post('sns', GetSnsNotification::class);
