<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Jan 2025 11:56:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

Route::prefix("billing")
    ->name("billing.")
    ->group(__DIR__."/billing.php");

Route::prefix("storage")
    ->name("storage.")
    ->group(__DIR__."/storage.php");
