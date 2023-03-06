<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


use App\Actions\Procurement\Agent\IndexAgents;
use App\Actions\Procurement\Agent\ShowAgent;
use App\Actions\Procurement\Supplier\IndexSuppliers;
use App\Actions\Procurement\Supplier\ShowSupplier;
use App\Actions\Procurement\SupplierProduct\IndexSupplierProducts;
use App\Actions\UI\Procurement\ProcurementDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ProcurementDashboard::class)->name('dashboard');
Route::get('/suppliers', IndexSuppliers::class)->name('suppliers.index');
Route::get('/suppliers/{supplier}', ShowSupplier::class)->name('suppliers.show');


Route::get('/agents', IndexAgents::class)->name('agents.index');
Route::get('/agents/{agent}', ShowAgent::class)->name('agents.show');
Route::get('/agents/{agent}/suppliers', [IndexSuppliers::class, 'inAgent'])->name('agents.show.suppliers.index');
Route::get('/agents/{agent}/suppliers/{supplier}', [ShowSupplier::class, 'inAgent'])->name('agents.show.suppliers.show');

Route::get('/supplier-products', IndexSupplierProducts::class)->name('supplier-products.index');
