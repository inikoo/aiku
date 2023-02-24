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
use Illuminate\Support\Facades\Route;

Route::get('/', ShowProcurementDashboard::class)->name('dashboard');
Route::get('/suppliers', IndexSuppliers::class)->name('suppliers.index');
Route::get('/suppliers/{supplier}', ShowSupplier::class)->name('suppliers.show');


Route::get('/agents', IndexAgents::class)->name('agents.index');
Route::get('/agents/{agent}', ShowAgent::class)->name('agents.show');
Route::get('/agents/{agent}/suppliers', [IndexSuppliers::class, 'inAgent'])->name('agents.show.suppliers.index');
Route::get('/agents/{agent}/suppliers/{supplier}', [ShowSupplier::class, 'inAgent'])->name('agents.show.suppliers.show');


