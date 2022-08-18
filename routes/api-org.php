<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 18 Aug 2022 15:28:35 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use App\Actions\Organisations\UserLinkCode\StoreUserLinkStore;
use Illuminate\Support\Facades\Route;



Route::post('/user-link-codes', StoreUserLinkStore::class)->name('user-link-codes.store');
