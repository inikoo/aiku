<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Procurement\Marketplace\Agent\UI\CreateMarketplaceAgent;
use App\Actions\Procurement\Marketplace\Agent\UI\EditMarketplaceAgent;
use App\Actions\Procurement\Marketplace\Agent\UI\IndexMarketplaceAgents;
use App\Actions\Procurement\Marketplace\Agent\UI\ShowMarketplaceAgent;
use App\Actions\Procurement\Marketplace\Supplier\UI\CreateMarketplaceSupplier;
use App\Actions\Procurement\Marketplace\Supplier\UI\EditMarketplaceSupplier;
use App\Actions\Procurement\Marketplace\Supplier\UI\IndexMarketplaceSuppliers;
use App\Actions\Procurement\Marketplace\Supplier\UI\ShowMarketplaceSupplier;
use App\Actions\Procurement\Marketplace\SupplierProduct\UI\IndexMarketplaceSupplierProducts;
use App\Actions\Procurement\Marketplace\SupplierProduct\UI\ShowMarketplaceSupplierProduct;
use Illuminate\Support\Facades\Route;

Route::get('/agents', IndexMarketplaceAgents::class)->name('agents.index');
Route::get('/agents/create', CreateMarketplaceAgent::class)->name('agents.create');
Route::get('/agents/{agent}', ShowMarketplaceAgent::class)->name('agents.show');
Route::get('/agents/{agent}/edit', EditMarketplaceAgent::class)->name('agents.edit');
Route::get('/agents/{agent}/suppliers', [IndexMarketplaceSuppliers::class, 'inAgent'])->name('agents.show.suppliers.index');
Route::get('/agents/{agent}/suppliers/create', [CreateMarketplaceSupplier::class, 'inAgent'])->name('agents.show.suppliers.create');

Route::get('/agents/{agent}/supplier-products', [IndexMarketplaceSupplierProducts::class, 'inAgent'])->name('agents.show.supplier-products.index');
Route::get('/agents/{agent}/supplier-products/{supplierProduct}', [ShowMarketplaceSupplierProduct::class, 'inAgent'])->name('agents.show.supplier-products.show');


Route::get('/agents/{agent}/suppliers/{supplier}', [ShowMarketplaceSupplier::class, 'inAgent'])->name('agents.show.suppliers.show');
Route::get('/agents/{agent}/suppliers/{supplier}/edit', [EditMarketplaceSupplier::class, 'inAgent'])->name('agents.show.suppliers.edit');

Route::get('/agents/{agent}/suppliers/{supplier}/supplier-products', [IndexMarketplaceSupplierProducts::class, 'inSupplierInAgent'])->name('agents.show.suppliers.show.supplier-products.index');
Route::get('/agents/{agent}/suppliers/{supplier}/supplier-products/{supplierProduct}', [ShowMarketplaceSupplierProduct::class, 'inSupplierInAgent'])->name('agents.show.suppliers.show.supplier-products.show');

Route::get('/suppliers', IndexMarketplaceSuppliers::class)->name('suppliers.index');
Route::get('/suppliers/create', CreateMarketplaceSupplier::class)->name('suppliers.create');
Route::get('/suppliers/{supplier}', ShowMarketplaceSupplier::class)->name('suppliers.show');
Route::get('/suppliers/{supplier}/edit', EditMarketplaceSupplier::class)->name('suppliers.edit');
Route::get('/suppliers/{supplier}/supplier-products', [IndexMarketplaceSupplierProducts::class, 'inSupplier'])->name('suppliers.show.supplier-products.index');
Route::get('/suppliers/{supplier}/supplier-products/{supplierProduct}', [ShowMarketplaceSupplierProduct::class, 'inSupplier'])->name('suppliers.show.supplier-products.show');


Route::get('/supplier-products/{supplierProduct}', ShowMarketplaceSupplierProduct::class)->name('supplier-products.show');
