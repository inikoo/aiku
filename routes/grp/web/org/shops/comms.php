<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:06:20 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Comms\OrgPostRoom\UI\IndexOrgPostRoom;
use App\Actions\Comms\Outbox\UI\IndexOutboxes;
use App\Actions\Comms\Outbox\UI\ShowOutbox;
use App\Actions\Comms\Outbox\UI\ShowOutboxWorkshop;
use App\Actions\Comms\ShowCommsDashboard;
use Illuminate\Support\Facades\Route;

Route::get('', ShowCommsDashboard::class)->name('dashboard');
Route::get('outboxes', [IndexOutboxes::class, 'inShop'])->name('outboxes.index');
Route::get('outboxes/{outbox}', [ShowOutbox::class, 'inShop'])->name('outboxes.show');
Route::get('outboxes/{outbox}/workshop', ShowOutboxWorkshop::class)->name('outboxes.workshop');

Route::get('post-rooms', [IndexOrgPostRoom::class, 'inShop'])->name('post-rooms.index');