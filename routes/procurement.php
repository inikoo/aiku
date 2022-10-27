<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 12:11:26 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Procurement\Agent\IndexAgents;
use App\Actions\Procurement\Agent\ShowAgent;
use App\Actions\Procurement\ShowProcurementDashboard;
use App\Actions\Procurement\Supplier\IndexSuppliers;
use App\Actions\Procurement\Supplier\ShowSupplier;

Route::get('/', ShowProcurementDashboard::class)->name('dashboard');
Route::get('/suppliers', IndexSuppliers::class)->name('suppliers.index');
Route::get('/suppliers/{supplier:slug}', ShowSupplier::class)->name('suppliers.show');


Route::get('/agents', IndexAgents::class)->name('agents.index');
Route::get('/agents/{agent:slug}', ShowAgent::class)->name('agents.show');

