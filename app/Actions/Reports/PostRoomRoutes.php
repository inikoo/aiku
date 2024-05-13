<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 14:54:29 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Reports;

use App\Actions\Mail\Mailroom\IndexMailrooms;
use Illuminate\Support\Facades\Route;

class PostRoomRoutes
{
    public function __invoke($parent): void
    {
        Route::get('/mailrooms', [IndexMailrooms::class, $parent == 'organisation' ? 'inOrganisation' : 'inShop'])->name('mailrooms.index');

    }
}
