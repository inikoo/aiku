<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jan 2024 03:24:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Devel\UI\IndexDummies;
use App\Actions\UI\Reports\IndexReports;
use App\Actions\Reports\PostRoomRoutes;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexReports::class)->name('index');


Route::name("sent_emails.")->prefix('sent-emails')
    ->group(function () {
        $postRoomRoutes = new PostRoomRoutes();
        $postRoomRoutes('organisation');
        //  Route::get('shops', IndexDummies::class)->name('shops.index');
        Route::name("shops.")->prefix('shops')
            ->group(function () {
                Route::get('', IndexDummies::class)->name('index');

                Route::get('{shop}', IndexDummies::class)->name('shop');

                Route::name("show.")->prefix('{shop}')
                    ->group(
                        function () {
                            $postRoomRoutes = new PostRoomRoutes();
                            $postRoomRoutes('shop');
                        }
                    );
            });
    });
