<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 04 Mar 2025 19:11:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use App\Actions\Inventory\UI\ShowAgentInventoryDashboard;
use App\Actions\SupplyChain\SupplierProduct\UI\IndexSupplierProducts;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowAgentInventoryDashboard::class)->name('dashboard');
Route::get('/products', IndexSupplierProducts::class)->name('supplier_products.index');
