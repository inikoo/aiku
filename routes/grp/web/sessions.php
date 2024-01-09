<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 Mar 2023 16:09:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\UI\Sessions\DeleteCurrentShopSession;
use App\Actions\UI\Sessions\UpdateCurrentShopSession;
use Illuminate\Support\Facades\Route;

Route::patch('/update-current-shop/{shop}', UpdateCurrentShopSession::class)->name('current-shop.update');
Route::delete('/delete-current-shop/', DeleteCurrentShopSession::class)->name('current-shop.delete');
