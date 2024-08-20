<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 Jan 2024 22:05:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


Route::prefix("org/{organisation:id}")
    ->name("org.")
    ->group(function () {
        Route::prefix("hr")
            ->name("hr.")
            ->group(__DIR__."/org/hr.php");
        Route::prefix("warehouses")
            ->name("warehouses.")
            ->group(__DIR__."/org/warehouses.php");
        Route::prefix("inventory")
            ->name("inventory.")
            ->group(__DIR__."/org/inventory.php");
        Route::prefix("locations")
            ->name("locations.")
            ->group(__DIR__."/org/locations.php");
        Route::prefix("procurement")
            ->name("procurement.")
            ->group(__DIR__."/org/procurement.php");
    });
