<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 04 Mar 2025 19:11:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use App\Actions\Inventory\UI\ShowAgentInventoryDashboard;
use App\Actions\SupplyChain\SupplierProduct\UI\IndexAgentSupplierProducts;
use App\Actions\SupplyChain\SupplierProduct\UI\ShowSupplierProduct;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowAgentInventoryDashboard::class)->name('dashboard');
Route::get('/skus', IndexAgentSupplierProducts::class)->name('supplier_products.index');
Route::get('/skus/{supplierProduct}', ShowSupplierProduct::class)->name('supplier_products.show');
