<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Jan 2024 15:34:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\SysAdmin\Organisation\UI\ShowOrganisationDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowOrganisationDashboard::class)->name('show');
