

<?php
/*
 * Author: Vika Aqordi <aqordivika@yahoo.co.id>
 * Created on: 19-08-2024, Bali, Indonesia
 * Github: https://github.com/aqordeon
 * Copyright: 2024
 *
*/

use App\Actions\SysAdmin\User\UI\CreateUser;
use App\Actions\SysAdmin\User\UI\EditUser;
use App\Actions\SysAdmin\User\UI\IndexUsers;
use App\Actions\SysAdmin\User\UI\ShowUser;
use App\Actions\UI\Retina\Dropshipping\ShowDropshipping;
use App\Actions\UI\Retina\Dropshipping\IndexProducts;
use Illuminate\Support\Facades\Route;

Route::get('/', ShowDropshipping::class)->name('dashboard');
Route::get('/products', IndexProducts::class)->name('products.index');

// Route::get('/users', IndexUsers::class)->name('web-users.index');
// Route::get('/users/create', CreateUser::class)->name('web-users.create');
// Route::get('/users/{user}', ShowUser::class)->name('web-users.show');
// Route::get('/users/{user}/edit', EditUser::class)->name('web-users.edit');
