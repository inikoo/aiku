<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 27 Jun 2023 14:15:16 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Auth\User\GetAllUsers;
use App\Actions\Inventory\Location\GetLocations;
use Illuminate\Support\Facades\Route;

Route::get('/users', GetAllUsers::class)->name('users');
Route::get('/locations', GetLocations::class)->name('locations');
