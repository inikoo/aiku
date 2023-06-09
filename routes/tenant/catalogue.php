<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 31 Mar 2023 11:30:06 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Actions\Marketing\ProductCategory\UI\CreateDepartment;
use App\Actions\Marketing\ProductCategory\UI\CreateFamily;
use App\Actions\Marketing\ProductCategory\UI\EditDepartment;
use App\Actions\Marketing\ProductCategory\UI\EditFamily;
use App\Actions\Marketing\ProductCategory\UI\IndexDepartments;
use App\Actions\Marketing\ProductCategory\UI\IndexFamilies;
use App\Actions\Marketing\ProductCategory\UI\ShowDepartment;

use App\Actions\Marketing\Product\UI\CreateProduct;
use App\Actions\Marketing\Product\UI\EditProduct;
use App\Actions\Marketing\Product\UI\IndexProducts;
use App\Actions\Marketing\Product\UI\ShowProduct;
use App\Actions\Marketing\ProductCategory\UI\ShowFamily;
use App\Actions\UI\Catalogue\CatalogueHub;
use Illuminate\Support\Facades\Route;

Route::get('/products/create', CreateProduct::class)->name('products.create');
//Route::get('/families/create', CreateFamily::class)->name('families.create');

Route::get('/departments', [IndexDepartments::class, 'inTenant'])->name('departments.index');
Route::get('/departments/{department}', [ShowDepartment::class, 'inTenant'])->name('departments.show');
Route::get('/departments/{department}/edit', [EditDepartment::class, 'inTenant'])->name('departments.edit');
Route::get('/families', [IndexFamilies::class, 'inTenant'])->name('families.index');
Route::get('/families/{department}', [ShowDepartment::class, 'inTenant'])->name('families.show');
Route::get('/families/{department}/edit', [EditDepartment::class, 'inTenant'])->name('families.edit');

Route::get('/', [CatalogueHub::class, 'inTenant'])->name('hub');
Route::get('/products', [IndexProducts::class,'inTenant'])->name('products.index');
Route::get('/products/{product}', [ShowProduct::class, 'inTenant'])->name('products.show');
Route::get('/products/{product}/edit', [EditProduct::class, 'inTenant'])->name('products.edit');

Route::get('/{shop}', [CatalogueHub::class, 'inShop'])->name('shop.hub');
Route::get('/{shop}/products', [IndexProducts::class, 'inShop'])->name('shop.products.index');
Route::get('/{shop}/products/{product}', [ShowProduct::class, 'inShop'])->name('shop.products.show');
Route::get('/{shop}/products/{product}/edit', [EditProduct::class, 'inShop'])->name('shop.products.edit');




Route::get('/{shop}/departments/create', CreateDepartment::class)->name('shop.departments.create');
Route::get('/{shop}/departments', [IndexDepartments::class, 'inShop'])->name('shop.departments.index');
Route::get('/{shop}/departments/{department}', [ShowDepartment::class, 'inShop'])->name('shop.departments.show');
Route::get('/{shop}/departments/{department}/edit', [EditDepartment::class, 'inShop'])->name('shop.departments.edit');

Route::get('/{shop}/families/create', CreateFamily::class)->name('shop.families.create');
Route::get('/{shop}/families', [IndexFamilies::class, 'inShop'])->name('shop.families.index');
Route::get('/{shop}/families/{family}', [ShowFamily::class, 'inShop'])->name('shop.families.show');
Route::get('/{shop}/families/{family}/edit', [EditFamily::class, 'inShop'])->name('shop.families.edit');


//Route::get('/departments/{department}/families', [IndexFamilies::class, $parent == 'tenant' ? 'inDepartment' : 'inDepartmentInShop'])->name('departments.show.families.index');
//Route::get('/departments/{department}/families/{family}', [ShowFamily::class, $parent == 'tenant' ? 'inDepartment' : 'inDepartmentInShop'])->name('departments.show.families.show');
//Route::get('/departments/{department}/families/{family}/edit', [EditFamily::class, $parent == 'tenant' ? 'inDepartment' : 'inDepartmentInShop'])->name('departments.show.families.edit');
Route::get('/departments/{department}/products', [IndexProducts::class,  'inDepartment' ])->name('departments.show.products.index');
//Route::get('/families', [IndexFamilies::class, 'inShop'])->name('families.index');
//Route::get('/families/{family}', [ShowFamily::class, 'inShop'])->name('families.show');
//Route::get('/families/{family}/edit', [EditFamily::class, 'inShop'])->name('families.edit');
