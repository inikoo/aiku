<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 11:31:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Collection\UI\IndexCollection;
use App\Actions\Catalogue\Collection\UI\ShowCollection;
use App\Actions\Catalogue\Asset\UI\CreateProduct;
use App\Actions\Catalogue\Asset\UI\EditProduct;
use App\Actions\Catalogue\Asset\UI\IndexProducts;
use App\Actions\Catalogue\Asset\UI\ShowProduct;
use App\Actions\Catalogue\ProductCategory\UI\CreateDepartment;
use App\Actions\Catalogue\ProductCategory\UI\CreateFamily;
use App\Actions\Catalogue\ProductCategory\UI\EditDepartment;
use App\Actions\Catalogue\ProductCategory\UI\EditFamily;
use App\Actions\Catalogue\ProductCategory\UI\IndexDepartments;
use App\Actions\Catalogue\ProductCategory\UI\IndexFamilies;
use App\Actions\Catalogue\ProductCategory\UI\ShowDepartment;
use App\Actions\Catalogue\ProductCategory\UI\ShowFamily;
use App\Actions\Catalogue\Shop\UI\ShowCatalogue;
use App\Actions\Devel\UI\CreateDummy;
use App\Actions\Devel\UI\EditDummy;
use Illuminate\Support\Facades\Route;

Route::get('', ShowCatalogue::class)->name('dashboard');


Route::name("products.")->prefix('products')
    ->group(function () {
        Route::get('', IndexProducts::class)->name('index');
        Route::get('create', CreateProduct::class)->name('create');

        Route::prefix('{product}')->group(function () {
            Route::get('', ShowProduct::class)->name('show');
            Route::get('edit', EditProduct::class)->name('edit');
        });
    });

Route::name("departments.")->prefix('departments')
    ->group(function () {
        Route::get('', IndexDepartments::class)->name('index');
        Route::get('create', CreateDepartment::class)->name('create');



        Route::prefix('{department}')->group(function () {
            Route::get('', ShowDepartment::class)->name('show');
            Route::get('edit', EditDepartment::class)->name('edit');
            Route::get('families', [IndexFamilies::class, 'inDepartment'])->name('families.index');
            Route::get('families/{family}', [ShowFamily::class, 'inDepartment'])->name('families.show');
            Route::get('products/{product}', [ShowProduct::class, 'inDepartment'])->name('products.show');
        });
    });

Route::name("families.")->prefix('families')
    ->group(function () {
        Route::get('', IndexFamilies::class)->name('index');
        Route::get('create', CreateFamily::class)->name('create');

        Route::prefix('{family}')->group(function () {
            Route::get('', [ShowFamily::class, 'inShop'])->name('show');
            Route::get('edit', [EditFamily::class, 'inShop'])->name('edit');
            Route::get('products/{product}', [ShowProduct::class, 'inFamily'])->name('products.show');
        });
    });

Route::name("collections.")->prefix('collections')
    ->group(function () {
        Route::get('', IndexCollection::class)->name('index');
        Route::get('create', CreateDummy::class)->name('create');

        Route::prefix('{collection}')->group(function () {
            Route::get('', ShowCollection::class)->name('show');
            Route::get('edit', EditDummy::class)->name('edit');
            Route::get('products/{product}', [ShowProduct::class, 'inCollection'])->name('products.show');
        });
    });
