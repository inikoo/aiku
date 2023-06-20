<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 16:27:58 Central European Summer, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Actions\Marketing\Product\UI\CreateProduct;
use App\Actions\Marketing\Product\UI\EditProduct;
use App\Actions\Marketing\Product\UI\IndexProducts;
use App\Actions\Marketing\Product\UI\ShowProduct;
use App\Actions\Marketing\ProductCategory\UI\CreateDepartment;
use App\Actions\Marketing\ProductCategory\UI\CreateFamily;
use App\Actions\Marketing\ProductCategory\UI\EditDepartment;
use App\Actions\Marketing\ProductCategory\UI\EditFamily;
use App\Actions\Marketing\ProductCategory\UI\IndexDepartments;
use App\Actions\Marketing\ProductCategory\UI\IndexFamilies;
use App\Actions\Marketing\ProductCategory\UI\ShowDepartment;
use App\Actions\Marketing\ProductCategory\UI\ShowFamily;
use App\Actions\Marketing\Shop\UI\CreateShop;
use App\Actions\Marketing\Shop\UI\EditShop;
use App\Actions\Marketing\Shop\UI\IndexShops;
use App\Actions\Marketing\Shop\UI\CreateShopsBySpreadSheet;
use App\Actions\Marketing\Shop\UI\ShowShop;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexShops::class)->name('index');
Route::get('/create', CreateShop::class)->name('create');
Route::get('/create-multi', CreateShopsBySpreadSheet::class)->name('create-multi');

Route::get('/{shop}', ShowShop::class)->name('show');
Route::get('/{shop}/edit', EditShop::class)->name('edit');




Route::get('/departments', [IndexDepartments::class, 'inTenant'])->name('departments.index');
Route::get('/departments/{department}', [ShowDepartment::class, 'inTenant'])->name('departments.show');
Route::get('/departments/{department}/edit', [EditDepartment::class, 'inTenant'])->name('departments.edit');
Route::get('/families', [IndexFamilies::class, 'inTenant'])->name('families.index');
Route::get('/families/{department}', [ShowDepartment::class, 'inTenant'])->name('families.show');
Route::get('/families/{department}/edit', [EditDepartment::class, 'inTenant'])->name('families.edit');

Route::get('/products', [IndexProducts::class,'inTenant'])->name('products.index');
Route::get('/products/{product}', [ShowProduct::class, 'inTenant'])->name('products.show');
Route::get('/products/{product}/edit', [EditProduct::class, 'inTenant'])->name('products.edit');


Route::get('/{shop}', ShowShop::class)->name('show');
Route::get('/{shop}/products', [IndexProducts::class, 'inShop'])->name('show.products.index');
Route::get('/shops/{shop}/products/create', CreateProduct::class)->name('show.products.create');

Route::get('/{shop}/products/{product}', [ShowProduct::class, 'inShop'])->name('show.products.show');
Route::get('/{shop}/products/{product}/edit', [EditProduct::class, 'inShop'])->name('show.products.edit');




Route::get('/{shop}/departments/create', CreateDepartment::class)->name('show.departments.create');
Route::get('/{shop}/departments', [IndexDepartments::class, 'inShop'])->name('show.departments.index');
Route::get('/{shop}/departments/{department}', [ShowDepartment::class, 'inShop'])->name('show.departments.show');
Route::get('/{shop}/departments/{department}/edit', [EditDepartment::class, 'inShop'])->name('show.departments.edit');

Route::get('/{shop}/families/create', CreateFamily::class)->name('show.families.create');
Route::get('/{shop}/families', [IndexFamilies::class, 'inShop'])->name('show.families.index');
Route::get('/{shop}/families/{family}', [ShowFamily::class, 'inShop'])->name('show.families.show');
Route::get('/{shop}/families/{family}/edit', [EditFamily::class, 'inShop'])->name('show.families.edit');


//Route::get('/departments/{department}/families', [IndexFamilies::class, $parent == 'tenant' ? 'inDepartment' : 'inDepartmentInShop'])->name('departments.show.families.index');
//Route::get('/departments/{department}/families/{family}', [ShowFamily::class, $parent == 'tenant' ? 'inDepartment' : 'inDepartmentInShop'])->name('departments.show.families.show');
//Route::get('/departments/{department}/families/{family}/edit', [EditFamily::class, $parent == 'tenant' ? 'inDepartment' : 'inDepartmentInShop'])->name('departments.show.families.edit');
Route::get('/departments/{department}/products', [IndexProducts::class,  'inDepartment' ])->name('departments.show.products.index');
//Route::get('/families', [IndexFamilies::class, 'inShop'])->name('families.index');
//Route::get('/families/{family}', [ShowFamily::class, 'inShop'])->name('families.show');
//Route::get('/families/{family}/edit', [EditFamily::class, 'inShop'])->name('families.edit');
