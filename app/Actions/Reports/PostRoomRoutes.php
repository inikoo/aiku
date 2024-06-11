<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 14:54:29 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Reports;

use App\Actions\Mail\PostRoom\IndexPostRooms;
use Illuminate\Support\Facades\Route;

class PostRoomRoutes
{
    public function __invoke($parent): void
    {
        Route::get('/post_rooms', [IndexPostRooms::class, $parent == 'organisation' ? 'inOrganisation' : 'inShop'])->name('post_rooms.index');

    }
}
