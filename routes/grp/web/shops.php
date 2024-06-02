<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 11 Jan 2024 03:24:21 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Actions\Catalogue\Asset\UI\CreateProduct;
use App\Actions\Catalogue\Asset\UI\EditProduct;
use App\Actions\Catalogue\Asset\UI\IndexProducts;
use App\Actions\Catalogue\Asset\UI\RemoveProduct;
use App\Actions\Catalogue\Asset\UI\ShowProduct;
use App\Actions\Catalogue\ProductCategory\ExportProductCategory;
use App\Actions\Catalogue\ProductCategory\UI\CreateDepartment;
use App\Actions\Catalogue\ProductCategory\UI\CreateDepartments;
use App\Actions\Catalogue\ProductCategory\UI\CreateFamily;
use App\Actions\Catalogue\ProductCategory\UI\EditDepartment;
use App\Actions\Catalogue\ProductCategory\UI\EditFamily;
use App\Actions\Catalogue\ProductCategory\UI\IndexDepartments;
use App\Actions\Catalogue\ProductCategory\UI\IndexFamilies;
use App\Actions\Catalogue\ProductCategory\UI\RemoveDepartment;
use App\Actions\Catalogue\ProductCategory\UI\RemoveFamily;
use App\Actions\Catalogue\ProductCategory\UI\ShowDepartment;
use App\Actions\Catalogue\ProductCategory\UI\ShowFamily;
use App\Actions\Catalogue\Shop\ExportShops;
use Illuminate\Support\Facades\Route;

/*
Route::get('/departments', [IndexDepartments::class, 'inOrganisation'])->name('departments.index');
Route::get('/departments/export', ExportProductCategory::class)->name('departments.export');

Route::get('/departments/create', ExportProductCategory::class)->name('departments.create');
Route::get('/departments/{department}', [ShowDepartment::class, 'inOrganisation'])->name('departments.show');
Route::get('/departments/{department}/edit', [EditDepartment::class, 'inOrganisation'])->name('departments.edit');
Route::get('/departments/{department}/delete', [RemoveDepartment::class,'inOrganisation'])->name('departments.remove');
Route::get('/families', [IndexFamilies::class, 'inOrganisation'])->name('families.index');
Route::get('/families/{family}', [ShowFamily::class, 'inOrganisation'])->name('families.show');
Route::get('/families/{family}/edit', [EditFamily::class, 'inOrganisation'])->name('families.edit');

Route::get('/products', [IndexProducts::class,'inOrganisation'])->name('products.index');
Route::get('/products/{product}', [ShowProduct::class, 'inOrganisation'])->name('products.show');
Route::get('/products/{product}/edit', [EditProduct::class, 'inOrganisation'])->name('products.edit');

Route::get('/export', ExportShops::class)->name('export');





Route::get('/{shop}/products', [IndexProducts::class, 'inShop'])->name('show.products.index');
Route::get('/shops/{shop}/products/create', CreateProduct::class)->name('show.products.create');

Route::get('/{shop}/products/{product}', [ShowProduct::class, 'inShop'])->name('show.products.show');
Route::get('/{shop}/products/{product}/edit', [EditProduct::class, 'inShop'])->name('show.products.edit');
Route::get('/{shop}/products/{product}/delete', [RemoveProduct::class, 'inShop'])->name('show.products.remove');



Route::get('/{shop}/departments/create', CreateDepartment::class)->name('show.departments.create');
Route::get('/{shop}/departments/create-multi', CreateDepartments::class)->name('show.departments.create-multi');
Route::get('/{shop}/departments', [IndexDepartments::class, 'inShop'])->name('show.departments.index');
Route::get('/{shop}/departments/{department}', [ShowDepartment::class, 'inShop'])->name('show.departments.show');
Route::get('/{shop}/departments/{department}/edit', [EditDepartment::class, 'inShop'])->name('show.departments.edit');
Route::get('/{shop}/departments/{department}/delete', [RemoveDepartment::class, 'inShop'])->name('show.departments.remove');

Route::get('/{shop}/families/create', CreateFamily::class)->name('show.families.create');
Route::get('/{shop}/families', [IndexFamilies::class, 'inShop'])->name('show.families.index');
Route::get('/{shop}/families/{family}', [ShowFamily::class, 'inShop'])->name('show.families.show');
Route::get('/{shop}/families/{family}/edit', [EditFamily::class, 'inShop'])->name('show.families.edit');
Route::get('/{shop}/families/{family}/delete', [RemoveFamily::class, 'inShop'])->name('show.families.remove');
*/

//Route::get('/departments/{department}/families', [IndexFamilies::class, $parent == 'tenant' ? 'inDepartment' : 'inDepartmentInShop'])->name('departments.show.families.index');
//Route::get('/departments/{department}/families/{family}', [ShowFamily::class, $parent == 'tenant' ? 'inDepartment' : 'inDepartmentInShop'])->name('departments.show.families.show');
//Route::get('/departments/{department}/families/{family}/edit', [EditFamily::class, $parent == 'tenant' ? 'inDepartment' : 'inDepartmentInShop'])->name('departments.show.families.edit');
//Route::get('/departments/{department}/products', [IndexProducts::class,  'inDepartment' ])->name('departments.show.products.index');
//Route::get('/families', [IndexFamilies::class, 'inShop'])->name('families.index');
//Route::get('/families/{family}', [ShowFamily::class, 'inShop'])->name('families.show');
//Route::get('/families/{family}/edit', [EditFamily::class, 'inShop'])->name('families.edit');
