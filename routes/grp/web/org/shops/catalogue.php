<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 11:31:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Product\UI\CreateProduct;
use App\Actions\Catalogue\Product\UI\EditProduct;
use App\Actions\Catalogue\Product\UI\IndexProducts;
use App\Actions\Catalogue\Product\UI\ShowProduct;
use App\Actions\Catalogue\ProductCategory\UI\CreateDepartment;
use App\Actions\Catalogue\ProductCategory\UI\CreateDepartments;
use App\Actions\Catalogue\ProductCategory\UI\CreateFamily;
use App\Actions\Catalogue\ProductCategory\UI\EditDepartment;
use App\Actions\Catalogue\ProductCategory\UI\EditFamily;
use App\Actions\Catalogue\ProductCategory\UI\IndexDepartments;
use App\Actions\Catalogue\ProductCategory\UI\IndexFamilies;
use App\Actions\Catalogue\ProductCategory\UI\ShowDepartment;
use App\Actions\Catalogue\ProductCategory\UI\ShowFamily;
use App\Actions\Catalogue\Shop\UI\ShowCatalogue;

Route::get('', ShowCatalogue::class)->name('dashboard');

Route::get('products', IndexProducts::class)->name('products.index');
Route::get('products/create', CreateProduct::class)->name('products.create');

Route::get('products/{product}', ShowProduct::class)->name('products.show');
Route::get('products/{product}/edit', EditProduct::class)->name('products.edit');



Route::get('departments/create', [CreateDepartment::class, 'inShop'])->name('departments.create');
Route::get('departments/create-multi', CreateDepartments::class)->name('departments.create-multi');
Route::get('departments', IndexDepartments::class)->name('departments.index');
Route::get('departments/{department}', ShowDepartment::class)->name('departments.show');
Route::get('departments/{department}/edit', [EditDepartment::class, 'inShop'])->name('departments.edit');


Route::get('families/create', [CreateFamily::class, 'inShop'])->name('families.create');
Route::get('families', IndexFamilies::class)->name('families.index');
Route::get('families/{family}',  [ShowFamily::class, 'inShop'])->name('families.show');
Route::get('families/{family}/edit', [EditFamily::class, 'inShop'])->name('families.edit');
