<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Procurement\Agent\UI\CreateMarketplaceAgent;
use App\Actions\Procurement\Agent\UI\IndexMarketAgents;
use App\Actions\Procurement\Agent\UI\RemoveMarketplaceAgent;
use App\Actions\Procurement\Marketplace\Supplier\UI\CreateMarketplaceSupplier;
use App\Actions\Procurement\Marketplace\Supplier\UI\EditMarketplaceSupplier;
use App\Actions\Procurement\Marketplace\Supplier\UI\RemoveMarketplaceSupplier;
use App\Actions\Procurement\Marketplace\Supplier\UI\ShowMarketplaceSupplier;
use App\Actions\Procurement\Marketplace\SupplierProduct\UI\IndexMarketplaceSupplierProducts;
use App\Actions\Procurement\Marketplace\SupplierProduct\UI\ShowMarketplaceSupplierProduct;
use App\Actions\Procurement\Supplier\UI\IndexSuppliers;
use App\Actions\SupplyChain\Agent\UI\EditAgent;
use App\Actions\SupplyChain\Agent\UI\ShowAgent;
use Illuminate\Support\Facades\Route;

//todo: delete this
Route::get('/agents', IndexMarketAgents::class)->name('agents.index');
Route::get('/agents/create', CreateMarketplaceAgent::class)->name('agents.create');
Route::get('/agents/{agent}', ShowAgent::class)->name('agents.show')->withTrashed();

Route::get('/agents/{agent}/edit', EditAgent::class)->name('agents.edit');
Route::get('/agents/{agent}/delete', RemoveMarketplaceAgent::class)->name('agents.remove');

Route::get('/agents/{agent}/suppliers', [IndexSuppliers::class, 'inAgent'])->name('agents.show.suppliers.index');
Route::get('/agents/{agent}/suppliers/create', [CreateMarketplaceSupplier::class, 'inAgent'])->name('agents.show.suppliers.create');

Route::get('/agents/{agent}/supplier-products', [IndexMarketplaceSupplierProducts::class, 'inAgent'])->name('agents.show.supplier-products.index');
Route::get('/agents/{agent}/supplier-products/{supplierProduct}', [ShowMarketplaceSupplierProduct::class, 'inAgent'])->name('agents.show.supplier-products.show');

Route::get('/agents/{agent}/suppliers/{supplier}', [ShowMarketplaceSupplier::class, 'inMarketplaceAgent'])->name('agents.show.suppliers.show');
Route::get('/agents/{agent}/suppliers/{supplier}/edit', [EditMarketplaceSupplier::class, 'inMarketplaceAgent'])->name('agents.show.suppliers.edit');

Route::get('/agents/{agent}/suppliers/{supplier}/supplier-products', [IndexMarketplaceSupplierProducts::class, 'inSupplierInAgent'])->name('agents.show.suppliers.show.supplier-products.index');
Route::get('/agents/{agent}/suppliers/{supplier}/supplier-products/{supplierProduct}', [ShowMarketplaceSupplierProduct::class, 'inSupplierInAgent'])->name('agents.show.suppliers.show.supplier-products.show');

Route::get('/suppliers', IndexSuppliers::class)->name('suppliers.index');
Route::get('/suppliers/create', CreateMarketplaceSupplier::class)->name('suppliers.create');
Route::get('/suppliers/{supplier}', ShowMarketplaceSupplier::class)->name('suppliers.show');
Route::get('/suppliers/{supplier}/edit', EditMarketplaceSupplier::class)->name('suppliers.edit');
Route::get('/suppliers/{supplier}/delete', RemoveMarketplaceSupplier::class)->name('suppliers.remove');
Route::get('/suppliers/{supplier}/supplier-products', [IndexMarketplaceSupplierProducts::class, 'inSupplier'])->name('suppliers.show.supplier-products.index');
Route::get('/suppliers/{supplier}/supplier-products/{supplierProduct}', [ShowMarketplaceSupplierProduct::class, 'inSupplier'])->name('suppliers.show.supplier-products.show');

Route::get('/supplier-products', [IndexMarketplaceSupplierProducts::class, 'inAgent'])->name('supplier-products.index');
Route::get('/supplier-products/{supplierProduct}', [ShowMarketplaceSupplierProduct::class, 'inAgent'])->name('supplier-products.show');
