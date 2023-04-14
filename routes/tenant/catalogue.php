<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 31 Mar 2023 11:30:06 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Marketing\Department\UI\CreateDepartment;
use App\Actions\Marketing\Department\UI\EditDepartment;
use App\Actions\Marketing\Department\UI\IndexDepartments;
use App\Actions\Marketing\Department\UI\ShowDepartment;
use App\Actions\Marketing\Family\UI\CreateFamily;
use App\Actions\Marketing\Family\UI\EditFamily;
use App\Actions\Marketing\Family\UI\IndexFamilies;
use App\Actions\Marketing\Family\UI\ShowFamily;
use App\Actions\Marketing\Product\UI\CreateProduct;
use App\Actions\Marketing\Product\UI\EditProduct;
use App\Actions\Marketing\Product\UI\IndexProducts;
use App\Actions\Marketing\Product\UI\ShowProduct;
use App\Actions\UI\Catalogue\CatalogueHub;
use Illuminate\Support\Facades\Route;

if (empty($parent)) {
    $parent = 'tenant';
}
Route::get('/products/create', CreateProduct::class)->name('hub.products.create');
Route::get('/departments/create', CreateDepartment::class)->name('hub.departments.create');
Route::get('/families/create', CreateFamily::class)->name('hub.families.create');


Route::get('/', [CatalogueHub::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('hub');
Route::get('/products', [IndexProducts::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('hub.products.index');
Route::get('/products/{product}', [ShowProduct::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('hub.products.show');
Route::get('/products/{product}/edit', [EditProduct::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('hub.products.edit');
Route::get('/departments', [IndexDepartments::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('hub.departments.index');
Route::get('/departments/{department}', [ShowDepartment::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('hub.departments.show');
Route::get('/departments/{department}/edit', [EditDepartment::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('hub.departments.edit');
Route::get('/departments/{department}/families', [IndexFamilies::class, $parent == 'tenant' ? 'inDepartment' : 'inDepartmentInShop'])->name('hub.departments.show.families.index');
Route::get('/departments/{department}/families/{family}', [ShowFamily::class, $parent == 'tenant' ? 'inDepartment' : 'inDepartmentInShop'])->name('hub.departments.show.families.show');
Route::get('/departments/{department}/families/{family}/edit', [EditFamily::class, $parent == 'tenant' ? 'inDepartment' : 'inDepartmentInShop'])->name('hub.departments.show.families.edit');
Route::get('/departments/{department}/products', [IndexProducts::class, $parent == 'tenant' ? 'inDepartment' : 'inDepartmentInShop'])->name('hub.departments.show.products.index');
Route::get('/families', [IndexFamilies::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('hub.families.index');
Route::get('/families/{family}', [ShowFamily::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('hub.families.show');
Route::get('/families/{family}/edit', [EditFamily::class, $parent == 'tenant' ? 'inTenant' : 'inShop'])->name('hub.families.edit');
