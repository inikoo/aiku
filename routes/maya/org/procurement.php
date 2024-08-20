<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Aug 2024 16:53:37 Central Indonesia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */



use App\Actions\Inventory\OrgStock\UI\IndexOrgStocks;
use App\Actions\Inventory\OrgStock\UI\ShowOrgStock;
use App\Actions\Inventory\OrgStockFamily\UI\IndexOrgStockFamilies;
use App\Actions\Inventory\OrgStockFamily\UI\ShowOrgStockFamily;
use App\Actions\Procurement\OrgAgent\UI\IndexOrgAgents;
use App\Actions\Procurement\OrgAgent\UI\ShowOrgAgent;
use App\Actions\Procurement\OrgSupplier\UI\IndexOrgSuppliers;
use App\Actions\Procurement\OrgSupplier\UI\ShowOrgSupplier;
use Illuminate\Support\Facades\Route;

Route::prefix('agents')->as('org_agents.')->group(function () {
    Route::prefix('all')->as('all_org_agents.')->group(function () {
        Route::get('/', [IndexOrgAgents::class, 'maya'])->name('index');

        Route::prefix('{orgAgent:id}')->group(function () {
            Route::get('', [ShowOrgAgent::class,'maya'])->name('show')->withoutScopedBindings();
        });
    });
});

Route::prefix('suppliers')->as('org_suppliers.')->group(function () {
    Route::prefix('all')->as('all_org_suppliers.')->group(function () {
        Route::get('/', [IndexOrgSuppliers::class, 'maya'])->name('index');

        Route::prefix('{orgSupplier:id}')->group(function () {
            Route::get('', [ShowOrgSupplier::class,'maya'])->name('show')->withoutScopedBindings();
        });
    });
});
