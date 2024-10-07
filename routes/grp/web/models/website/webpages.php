
<?php
/*
 * author Arya Permana - Kirin
 * created on 07-10-2024-14h-55m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

use App\Actions\Catalogue\Collection\StoreCollectionWebpage;
use App\Actions\Catalogue\Product\StoreProductWebpage;
use App\Actions\Catalogue\ProductCategory\StoreProductCategoryWebpage;
use Illuminate\Support\Facades\Route;

Route::name('webpages.')->prefix('webpages')->group(function () {
    Route::post('{product:id}', StoreProductWebpage::class)->name('product.store');
    Route::post('{productCategory:id}', StoreProductCategoryWebpage::class)->name('product_category.store');
    Route::post('{collection:id}', StoreCollectionWebpage::class)->name('collection.store');
});
