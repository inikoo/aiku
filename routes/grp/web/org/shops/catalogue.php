<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jan 2024 11:31:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Actions\Catalogue\Asset\UI\EditProduct;
use App\Actions\Catalogue\Collection\UI\CreateCollection;
use App\Actions\Catalogue\Collection\UI\EditCollection;
use App\Actions\Catalogue\Collection\UI\IndexCollection;
use App\Actions\Catalogue\Collection\UI\ShowCollection;
use App\Actions\Catalogue\Product\UI\CreateProduct;
use App\Actions\Catalogue\Product\UI\EditProduct as UIEditProduct;
use App\Actions\Catalogue\Product\UI\IndexProducts;
use App\Actions\Catalogue\Product\UI\ShowProduct;
use App\Actions\Catalogue\ProductCategory\UI\CreateDepartment;
use App\Actions\Catalogue\ProductCategory\UI\CreateFamily;
use App\Actions\Catalogue\ProductCategory\UI\CreateSubDepartment;
use App\Actions\Catalogue\ProductCategory\UI\EditDepartment;
use App\Actions\Catalogue\ProductCategory\UI\EditFamily;
use App\Actions\Catalogue\ProductCategory\UI\EditSubDepartment;
use App\Actions\Catalogue\ProductCategory\UI\IndexDepartments;
use App\Actions\Catalogue\ProductCategory\UI\IndexFamilies;
use App\Actions\Catalogue\ProductCategory\UI\IndexSubDepartment;
use App\Actions\Catalogue\ProductCategory\UI\ShowDepartment;
use App\Actions\Catalogue\ProductCategory\UI\ShowFamily;
use App\Actions\Catalogue\ProductCategory\UI\ShowSubDepartment;
use App\Actions\Catalogue\Shop\UI\ShowCatalogue;
use Illuminate\Support\Facades\Route;

Route::get('', ShowCatalogue::class)->name('dashboard');


Route::name("products.")->prefix('products')
    ->group(function () {
        Route::get('', IndexProducts::class)->name('index');
        Route::get('create', CreateProduct::class)->name('create');

        Route::prefix('{product}')->group(function () {
            Route::get('', ShowProduct::class)->name('show');
            Route::get('edit', [UIEditProduct::class, 'inShop'])->name('edit');
        });
    });

Route::name("departments.")->prefix('departments')
    ->group(function () {
        Route::get('', IndexDepartments::class)->name('index');
        Route::get('create', CreateDepartment::class)->name('create');


        Route::get('{department}/edit', [EditDepartment::class, 'inShop'])->name('edit');
        Route::prefix('{department}')->name('show')->group(function () {
            Route::get('', ShowDepartment::class);
            Route::prefix('families')->name('.families.')->group(function () {
                Route::get('', [IndexFamilies::class, 'inDepartment'])->name('index');
                Route::get('create', [CreateFamily::class, 'inDepartment'])->name('create');

                Route::prefix('{family}')->group(function () {
                    Route::get('edit', [EditFamily::class, 'inDepartment'])->name('edit');
                    Route::get('', [ShowFamily::class, 'inDepartment'])->name('show');
                    Route::name("show.products.")->prefix('products')
                        ->group(function () {
                            Route::get('', [IndexProducts::class, 'inFamilyInDepartment'])->name('index');
                            Route::get('create', [CreateProduct::class, 'inFamilyInDepartment'])->name('create');

                            Route::prefix('{product}')->group(function () {
                                Route::get('', [ShowProduct::class, 'inFamilyInDepartment'])->name('show');
                                Route::get('edit', [UIEditProduct::class, 'inFamilyinDepartment'])->name('edit');
                            });
                        });
                });
            });
            Route::prefix('products')->name('.products.')->group(function () {
                Route::get('', [IndexProducts::class, 'inDepartment'])->name('index');
                Route::get('{product}', [ShowProduct::class, 'inDepartment'])->name('show');
                Route::get('edit/{product}', [UIEditProduct::class, 'inDepartment'])->name('edit');
            });
            Route::prefix('sub-departments')->name('.sub-departments.')->group(function () {
                Route::get('', [IndexSubDepartment::class, 'inDepartment'])->name('index');
                Route::get('create', [CreateSubDepartment::class, 'inDepartment'])->name('create');
                Route::get('edit/{subDepartment}', [EditSubDepartment::class, 'inDepartment'])->name('edit');
                Route::get('{subDepartment}', [ShowSubDepartment::class, 'inDepartment'])->name('show');

            });
        });
    });

Route::name("families.")->prefix('families')
    ->group(function () {
        Route::get('', IndexFamilies::class)->name('index');
        Route::get('create', CreateFamily::class)->name('create');

        Route::get('{family}/edit', [EditFamily::class, 'inShop'])->name('edit');

        Route::prefix('{family}')->name('show')->group(function () {
            Route::get('', [ShowFamily::class, 'inShop']);
            Route::prefix('products')->name('.products.')->group(function () {
                Route::get('', [IndexProducts::class, 'inFamily'])->name('index');
                Route::get('create', [CreateProduct::class, 'inFamily'])->name('create');
                Route::get('{product}', [ShowProduct::class, 'inFamily'])->name('show');
                Route::get('edit/{product}', [UIEditProduct::class, 'inFamily'])->name('edit');
            });
        });
    });

Route::name("collections.")->prefix('collections')
    ->group(function () {
        Route::get('', IndexCollection::class)->name('index');
        Route::get('create', CreateCollection::class)->name('create');

        Route::prefix('{collection}')->group(function () {
            Route::get('', ShowCollection::class)->name('show');
            Route::get('edit', EditCollection::class)->name('edit');
            Route::get('products/{product}', [ShowProduct::class, 'inCollection'])->name('products.show');
        });
    });
