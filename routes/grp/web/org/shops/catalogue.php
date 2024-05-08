<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 11:31:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Market\Product\UI\CreateProduct;
use App\Actions\Market\Product\UI\EditProduct;
use App\Actions\Market\Product\UI\IndexProducts;
use App\Actions\Market\Product\UI\ShowProduct;
use App\Actions\Market\ProductCategory\UI\CreateDepartment;
use App\Actions\Market\ProductCategory\UI\CreateDepartments;
use App\Actions\Market\ProductCategory\UI\CreateFamily;
use App\Actions\Market\ProductCategory\UI\EditDepartment;
use App\Actions\Market\ProductCategory\UI\EditFamily;
use App\Actions\Market\ProductCategory\UI\IndexDepartments;
use App\Actions\Market\ProductCategory\UI\IndexFamilies;
use App\Actions\Market\ProductCategory\UI\ShowDepartment;
use App\Actions\Market\ProductCategory\UI\ShowFamily;
use App\Actions\Market\Shop\UI\EditShop;
use App\Actions\Market\Shop\UI\ShowShop;

Route::get('', ShowShop::class)->name('dashboard');
Route::get('edit', EditShop::class)->name('edit');

Route::get('products', IndexProducts::class)->name('products.index');
Route::get('products/create', CreateProduct::class)->name('products.create');

Route::get('products/{product}', ShowProduct::class)->name('products.show');
Route::get('products/{product}/edit', EditProduct::class)->name('products.edit');



Route::get('departments/create', CreateDepartment::class)->name('departments.create');
Route::get('departments/create-multi', CreateDepartments::class)->name('departments.create-multi');
Route::get('departments', IndexDepartments::class)->name('departments.index');
Route::get('departments/{productCatalogue}', ShowDepartment::class)->name('departments.show');
Route::get('departments/{productCatalogue}/edit', EditDepartment::class)->name('departments.edit');


Route::get('families/create', CreateFamily::class)->name('families.create');
Route::get('families', IndexFamilies::class)->name('families.index');
Route::get('families/{family}', ShowFamily::class)->name('families.show');
Route::get('families/{family}/edit', EditFamily::class)->name('families.edit');
