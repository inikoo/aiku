<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 20 Jan 2023 14:20 Malaga Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get("/", function () {
    return Inertia::render("Dashboard");
})->name("show");
