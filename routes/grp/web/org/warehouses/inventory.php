<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 14:40:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Fulfilment\Pallet\UI\ShowPallet;
use App\Actions\UI\Fulfilment\ShowFulfilmentDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', ShowFulfilmentDashboard::class)->name('dashboard');
Route::get('/pallets', [IndexPallets::class, 'inWarehouse'])->name('pallets.index');
Route::get('/pallets/{pallet}', [ShowPallet::class, 'inWarehouse'])->name('pallets.show');
