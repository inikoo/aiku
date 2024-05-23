<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 12:33:52 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Dispatch\DeliveryNote\IndexDeliveryNotes;
use App\Actions\UI\Dispatch\ShowDispatchHub;

Route::get('/', ShowDispatchHub::class)->name('backlog');
Route::get('/delivery-notes', IndexDeliveryNotes::class)->name('delivery-notes');
