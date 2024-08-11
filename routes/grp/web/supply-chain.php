<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\SupplyChain\Agent\UI\CreateAgent;
use App\Actions\SupplyChain\Agent\UI\IndexAgents;
use App\Actions\SupplyChain\Agent\UI\ShowAgent;
use App\Actions\SupplyChain\Supplier\ExportSuppliers;
use App\Actions\SupplyChain\Supplier\UI\CreateSupplier;
use App\Actions\SupplyChain\Supplier\UI\EditSupplier;
use App\Actions\SupplyChain\Supplier\UI\IndexSuppliers;
use App\Actions\SupplyChain\Supplier\UI\ShowSupplier;
use App\Actions\SupplyChain\SupplierProduct\UI\IndexSupplierProducts;
use App\Actions\SupplyChain\SupplierProduct\UI\ShowSupplierProduct;
use App\Actions\SupplyChain\UI\ShowSupplyChainDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowSupplyChainDashboard::class)->name('dashboard');




Route::prefix("agents")->name("agents.")->group(
    function () {
        Route::get('', IndexAgents::class)->name('index');
        Route::get('create', CreateAgent::class)->name('create');
        Route::get('{agent}', ShowAgent::class)->name('show');

        Route::prefix('{agent}')->as('show')->group(function () {
            Route::get('', ShowAgent::class);
            Route::get('suppliers', [IndexSuppliers::class, 'inAgent'])->name('.suppliers.index');
            Route::get('suppliers/{supplier}', [ShowSupplier::class, 'inAgent'])->name('.suppliers.show');
            Route::get('suppliers/{supplier}/edit', [EditSupplier::class, 'inAgent'])->name('.suppliers.edit');
            Route::get('supplier-products', [IndexSupplierProducts::class, 'inAgent'])->name('.supplier_products.index');
            Route::get('supplier-products/{supplierProduct}', [ShowSupplierProduct::class, 'inAgent'])->name('.supplier_products.show');
        });

    }
);

Route::prefix("suppliers")->name("suppliers.")->group(
    function () {
        Route::get('', IndexSuppliers::class)->name('index');
        Route::get('create', CreateSupplier::class)->name('create');
        Route::get('export', ExportSuppliers::class)->name('export');
        Route::get('{supplier}', ShowSupplier::class)->name('show');
        Route::get('{supplier}/edit', EditSupplier::class)->name('edit');


    }
);

Route::prefix("supplier-products")->name("supplier_products.")->group(
    function () {
        Route::get('', IndexSupplierProducts::class)->name('index');
        Route::get('/{supplierProduct}', ShowSupplierProduct::class)->name('show');

    }
);
